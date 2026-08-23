<?php
// api/ai/AIService.php

require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/../../ai/ai_controller.php';
require_once __DIR__ . '/../common/constants.php';

class AIService {
    private $pdo;
    private $dbHelper;
    private $intents = null;
    private $intentCachePath;
    private $teacherId;
    private $groqApiKey;
    private $matchThreshold = 0.40; // 40% confidence threshold
    private $debug = false;
    
    /**
     * @param PDO $pdo
     * @param int $teacherId
     */
    public function __construct($pdo, $teacherId) {
        $this->pdo = $pdo;
        $this->dbHelper = new AIDatabaseHelper($pdo);
        $this->teacherId = $teacherId;
        $this->intentCachePath = __DIR__ . '/../../data/ai-responses.json';
        $this->groqApiKey = AIConfig::groqApiKey();
        $this->debug = getenv('AI_DEBUG') === '1';
    }

    /**
     * Process a user message using static-first strategy with AI fallback.
     *
     * Priority: Static Intent → Student Name → Gemini API → Fallback
     *
     * @param string $message
     * @return array{status:string,response:string,source:string}
     */
    public function processMessage(string $message): array {
        $message = trim($message);
        
        if (empty($message)) {
            return $this->error('Please enter a message');
        }

        try {
            // 1. Try static intent matching first (fastest, most reliable)
            $result = $this->matchStaticIntent($message);
            if ($result !== null) {
                return $result;
            }

            // 1b. Try command-based queries (e.g., phone number, parent name)
            $result = $this->matchCommandQuery($message);
            if ($result !== null) {
                return $result;
            }

            // 2. Try to match as student name (smart fallback for single-word queries like "Rahul")
            $result = $this->matchStudentName($message);
            if ($result !== null) {
                return $result;
            }

            // 2b. Check if message looks like a student name (proper noun, likely name)
            // If it's a short capitalized word, provide helpful feedback
            $wordCount = str_word_count($message);
            $isCapitalized = preg_match('/^[A-Z]/', $message);
            if ($wordCount <= 2 && $isCapitalized) {
                // User entered what looks like a name but it's not in system
                return [
                    'status' => 'success',
                    'response' => "I couldn't find a student named \"" . htmlspecialchars($message) . "\" in the system. " .
                                 "Please check the spelling or use the Students section to verify the exact name.",
                    'source' => 'not_found'
                ];
            }

            // 2c. Defensive check: if a student name can be extracted but not found, return a clean message
            $extracted = $this->extractStudentName($message);
            if ($extracted) {
                $student = $this->dbHelper->getStudentByName($extracted);
                if (!$student) {
                    return [
                        'status' => 'success',
                        'response' => "No student found with name: " . htmlspecialchars($extracted),
                        'source' => 'student_lookup'
                    ];
                }
            }

            // 3. If no static match and no student found, route to Groq AI
            if (!empty($this->groqApiKey)) {
                return $this->callGroqAPI($message);
            }
            if ($this->debug) {
                error_log("[EduSync AI] Groq key missing; routing to fallback");
            }

            // 4. Fallback if no API key
            return [
                'status' => 'success',
                'response' => 'I couldn\'t find a direct answer. Please check the dashboard or contact your administrator for more information.',
                'source' => 'fallback'
            ];
            
        } catch (Exception $e) {
            return $this->error('An error occurred processing your request');
        }
    }

    /**
     * Match message against static intents using keyword scoring.
     *
     * Algorithm:
     * - Tokenize message and compute best keyword match per intent.
     * - If any keyword matches, score = 1.0 else 0.0.
     *
     * @param string $message
     * @return array|null
     */
    private function matchStaticIntent(string $message): ?array {
        $intents = $this->loadIntents();
        if (empty($intents)) {
            return null;
        }

        // Step 1: Convert message to lowercase and split into words
        $messageLower = strtolower($message);
        $messageWords = $this->tokenizeMessage($messageLower);

        // Detect if the message mixes multiple topics (fees + attendance + marks)
        $topicTokens = [
            'fees' => ['fee','fees','balance','payment','due','pending','amount'],
            'attendance' => ['attendance','attend','present','absent'],
            'marks' => ['marks','score','scores','grade','grades','test','exam']
        ];
        $topicsDetected = 0;
        foreach ($topicTokens as $list) {
            foreach ($list as $t) {
                if (in_array($t, $messageWords, true)) {
                    $topicsDetected++;
                    break;
                }
            }
        }
        $hasAnd = str_contains($messageLower, ' and ');
        $multiTopic = ($topicsDetected >= 2) || ($hasAnd && $topicsDetected >= 1 && count($messageWords) > 4);

        $bestMatch = null;
        $bestScore = 0.0;
        $matchedIntents = 0;

        // Step 2 & 3: Evaluate each intent and calculate score
        foreach ($intents as $intent) {
            if (empty($intent['keywords']) || !is_array($intent['keywords'])) {
                continue; // Skip intents without keywords
            }

            $score = $this->scoreKeywordMatch($messageWords, $intent['keywords'], $messageLower);
            if ($score >= $this->matchThreshold) {
                $matchedIntents++;
            }
            // Step 4: Track the highest scoring intent
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $intent;
            }
        }

        // If multiple intents or multiple topics detected, let Gemini handle it
        if ($multiTopic && $matchedIntents >= 1) {
            return null;
        }

        // Step 5 & 6: Accept only if threshold met and not multi-topic
        if ($bestScore >= $this->matchThreshold && $bestMatch !== null) {
            return $this->processIntent($bestMatch, $message);
        }

        return null;
    }

    /**
     * Tokenize a message into lowercase words.
     *
     * @param string $message
     * @return array<int,string>
     */
    private function tokenizeMessage(string $message): array {
        // Remove punctuation except apostrophes/hyphens that separate words
        $message = preg_replace('/[^\w\s\'-]/', ' ', $message);
        
        // Split on whitespace, remove empty strings
        $words = preg_split('/\s+/', trim($message), -1, PREG_SPLIT_NO_EMPTY);
        
        return $words ?: [];
    }

    /**
     * Score keyword match using BEST match strategy.
     *
     * @param array<int,string> $tokens
     * @param array<int,string> $keywords
     * @param string $originalText
     * @return float
     */
    private function scoreKeywordMatch(array $tokens, array $keywords, string $originalText): float {
        if (empty($keywords)) {
            return 0.0;
        }

        // Use BEST match strategy: if ANY keyword matches, score = 1.0
        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            if (empty($keyword)) {
                continue; // Skip empty keywords
            }

            // If this keyword matches, intent is a perfect match
            if ($this->matchesKeyword($keyword, $tokens, $originalText)) {
                return 1.0; // Best possible score
            }
        }

        // No keywords matched
        return 0.0;
    }

    /**
     * Check if a keyword appears in message.
     *
     * @param string $keyword
     * @param array<int,string> $tokens
     * @param string $text
     * @return bool
     */
    private function matchesKeyword(string $keyword, array $tokens, string $text): bool {
        $keywordLower = strtolower($keyword);

        // Single-word keyword: check token array
        if (!str_contains($keywordLower, ' ')) {
            return in_array($keywordLower, $tokens, true);
        }

        // Multi-word keyword: check using word boundary regex (phrase matching)
        $pattern = '/\b' . preg_quote($keywordLower, '/') . '\b/i';
        return (bool)preg_match($pattern, $text);
    }

    /**
     * Try to match the message as a student name and return performance info.
     *
     * @param string $message
     * @return array|null
     */
    private function matchStudentName(string $message): ?array {
        // Only match short inputs (likely a name, not a full question)
        $wordCount = str_word_count($message);
        if ($wordCount > 3) {
            return null; // Too long to be just a name
        }

        try {
            // Try to find student by name in database
            $student = $this->dbHelper->getStudentByName($message);
            
            if ($student === null) {
                // Student not found - let other methods handle it
                return null;
            }

            // Build student performance info
            $studentData = $this->buildStudentData($student);
            
            // Format response with proper line breaks for chat display
            $response = "📊 Student: " . htmlspecialchars($studentData['student_name']) . "\n" .
                       "📅 Attendance: " . htmlspecialchars((string)$studentData['attendance']) . "%\n" .
                       "📈 Marks: " . htmlspecialchars((string)$studentData['marks']) . "/100\n" .
                       "✅ Status: " . htmlspecialchars((string)$studentData['status']);
            
            return [
                'status' => 'success',
                'response' => $response,
                'source' => 'student_lookup'
            ];
            
        } catch (Exception $e) {
            // Log error for debugging
            error_log("[EduSync AI] matchStudentName error for '{$message}': " . $e->getMessage());
            
            // Return null to let system try other methods (Gemini, fallback)
            return null;
        }
    }

    /**
     * Process a matched intent, including template substitution.
     *
     * @param array $intent
     * @param string $userMessage
     * @return array
     */
    private function processIntent(array $intent, string $userMessage): array {
        // If response_template exists, fetch database data and replace placeholders
        if (!empty($intent['response_template'])) {
            return $this->replaceTemplate($intent, $userMessage);
        }

        // Otherwise return static response
        return [
            'status' => 'success',
            'response' => $intent['response'] ?? 'Thank you for your query.',
            'source' => 'static'
        ];
    }

    /**
     * Replace template placeholders with database-backed values.
     *
     * @param array $intent
     * @param string $userMessage
     * @return array
     */
    private function replaceTemplate(array $intent, string $userMessage): array {
        try {
            $template = $intent['response_template'];
            $placeholders = $this->extractPlaceholders($template);

            // If no placeholders, return template as-is
            if (empty($placeholders)) {
                return [
                    'status' => 'success',
                    'response' => $template,
                    'source' => 'static'
                ];
            }

            // Extract student name from message
            $studentName = $this->extractStudentName($userMessage);
            if (empty($studentName)) {
                return [
                    'status' => 'success',
                    'response' => 'Please mention a student name to get detailed information.',
                    'source' => 'static'
                ];
            }

            // Fetch student data from database
            $student = $this->dbHelper->getStudentByName($studentName);
            if (empty($student)) {
                return [
                    'status' => 'success',
                    'response' => "No student found with name: " . htmlspecialchars($studentName),
                    'source' => 'static'
                ];
            }

            // Build complete student data for template replacement
            $studentData = $this->buildStudentData($student);

            // Replace all placeholders with actual values
            $response = $template;
            foreach ($placeholders as $placeholder) {
                $value = $this->getPlaceholderValue($studentData, $placeholder);
                $needle = '{{' . $placeholder . '}}';
                $response = str_replace($needle, $value, $response);
            }

            return [
                'status' => 'success',
                'response' => $response,
                'source' => 'static'
            ];
        } catch (Exception $e) {
            return $this->error('Error processing template');
        }
    }

    /**
     * Build complete student data by querying required aggregates.
     *
     * @param array $student
     * @return array
     */
    private function buildStudentData(array $student): array {
        try {
            // Validate student has ID
            if (empty($student['id'])) {
                throw new Exception("Student ID missing");
            }

            $studentId = (int)$student['id'];
            
            // Query database with individual error handling for each call
            $attendance = 0;
            try {
                $attendance = floatval($this->dbHelper->getStudentAttendance($studentId));
            } catch (Exception $e) {
                error_log("[EduSync AI] getStudentAttendance failed: " . $e->getMessage());
                $attendance = 0;
            }
            
            $marks = 0;
            try {
                $marks = floatval($this->dbHelper->getStudentMarks($studentId));
            } catch (Exception $e) {
                error_log("[EduSync AI] getStudentMarks failed: " . $e->getMessage());
                $marks = 0;
            }
            
            $status = "N/A";
            try {
                $status = strval($this->dbHelper->getStudentStatus($studentId));
            } catch (Exception $e) {
                error_log("[EduSync AI] getStudentStatus failed: " . $e->getMessage());
                $status = "N/A";
            }
            
            $balance = 0;
            try {
                $fees = (array)$this->dbHelper->getStudentFees($studentId);
                $balance = floatval($fees['balance'] ?? 0);
            } catch (Exception $e) {
                error_log("[EduSync AI] getStudentFees failed: " . $e->getMessage());
                $balance = 0;
            }

            // Contacts (defensive in case columns are absent)
            $phone = null;
            $parentName = null;
            $parentPhone = null;
            try {
                $contacts = (array)$this->dbHelper->getStudentContacts($studentId);
                $phone = $contacts['phone'] ?? null;
                $parentName = $contacts['parent_name'] ?? null;
                $parentPhone = $contacts['parent_phone'] ?? null;
            } catch (Exception $e) {
                $phone = null;
                $parentName = null;
                $parentPhone = null;
            }
            
            return [
                'id' => $studentId,
                'student_name' => $student['name'] ?? 'Unknown',
                'name' => $student['name'] ?? 'Unknown',
                'reg_no' => $student['reg_no'] ?? 'N/A',
                'email' => $student['email'] ?? 'N/A',
                'attendance' => $attendance,
                'marks' => $marks,
                'status' => $status,
                'fee_status' => $balance > 0 ? 'Pending' : 'Paid',
                'fees_status' => $balance > 0 ? 'Pending' : 'Paid',
                'pending_amount' => $balance,
                'phone' => $phone ?? 'N/A',
                'parent_name' => $parentName ?? 'N/A',
                'parent_phone' => $parentPhone ?? 'N/A'
            ];
            
        } catch (Exception $e) {
            // Return safe defaults if outer exception occurs
            error_log("[EduSync AI] buildStudentData outer error: " . $e->getMessage());
            return [
                'id' => $student['id'] ?? 0,
                'student_name' => $student['name'] ?? 'Unknown',
                'name' => $student['name'] ?? 'Unknown',
                'reg_no' => 'N/A',
                'email' => 'N/A',
                'attendance' => 0,
                'marks' => 0,
                'status' => 'N/A',
                'fee_status' => 'N/A',
                'fees_status' => 'N/A',
                'pending_amount' => 0
            ];
        }
    }

    /**
     * Extract all placeholder names from a template.
     *
     * @param string $template
     * @return array<int,string>
     */
    private function extractPlaceholders(string $template): array {
        preg_match_all('/\{\{(\w+)\}\}/', $template, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Extract a student name mentioned in a message by scanning known names.
     *
     * @param string $message
     * @return string|null
     */
    private function extractStudentName(string $message): ?string {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT name FROM students ORDER BY LENGTH(name) DESC LIMIT 100"
            );
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Search for student name (case-insensitive)
            // Sort by length DESC to match longest names first (avoid partial matches)
            foreach ($students as $name) {
                if (stripos($message, $name) !== false) {
                    return $name;
                }
            }
        } catch (Exception $e) {
            // Silently fail - handled by caller
        }

        return null;
    }

    /**
     * Get a placeholder value from the data array with formatting.
     *
     * @param array $data
     * @param string $placeholder
     * @return string
     */
    private function getPlaceholderValue(array $data, string $placeholder): string {
        $key = strtolower(str_replace('_', ' ', $placeholder));
        
        // Map placeholder names to data keys
        $value = null;
        if (isset($data[$placeholder])) {
            $value = $data[$placeholder];
        } elseif (isset($data[$key])) {
            $value = $data[$key];
        } else {
            return 'N/A';
        }

        if ($value === null) {
            return 'N/A';
        }

        // Format numeric values
        if (is_numeric($value) && is_float($value)) {
            return number_format((float)$value, 2);
        }

        return (string)$value;
    }

    /**
     * Call the Groq AI as a fallback when static intent doesn't match.
     *
     * @param string $message
     * @return array
     */
    private function callGroqAPI(string $message): array {
        try {
            $context = $this->buildConversationContext();
            $controller = new AIController($this->pdo, (int)$this->teacherId);
            $result = $controller->ask($message, $context);
            if ($this->debug) {
                $src = is_array($result) && isset($result['source']) ? $result['source'] : 'unknown';
                $len = is_array($result) && isset($result['response']) ? strlen((string)$result['response']) : 0;
                error_log("[EduSync AI] Groq ask() returned source={$src}, len={$len}");
            }
            if (empty($result['response'])) {
                return [
                    'status' => 'error',
                    'response' => 'AI service temporarily unavailable.',
                    'source' => 'error'
                ];
            }
            return [
                'status' => 'success',
                'response' => (string)$result['response'],
                'source' => 'ai'
            ];
        } catch (Exception $e) {
            error_log("[EduSync AI] Groq Exception: " . $e->getMessage());
            return [
                'status' => 'error',
                'response' => 'AI service temporarily unavailable.',
                'source' => 'error'
            ];
        }
    }

    /**
     * Return a soft, helpful fallback message when AI is unavailable.
     *
     * @return array{status:string,response:string,source:string}
     */
    private function fallbackResponse(): array {
        return [
            'status' => 'success',
            'response' => "I'm running in local mode and couldn't fetch an AI answer.\nTry: \"Attendance summary\", \"Marks of Rahul\", or check the dashboard for details.",
            'source' => 'internal'
        ];
    }


    /**
     * Load and cache intents from the ai-responses.json file.
     *
     * @return array<int,array>
     */
    private function loadIntents(): array {
        if ($this->intents === null) {
            try {
                $json = file_get_contents($this->intentCachePath);
                $this->intents = json_decode($json, true) ?? [];
            } catch (Exception $e) {
                $this->intents = [];
            }
        }
        return $this->intents;
    }

    /**
     * Command-based quick queries like phone number or parent info.
     *
     * @param string $message
     * @return array|null
     */
    private function matchCommandQuery(string $message): ?array {
        $text = strtolower(trim($message));
        $studentName = $this->extractStudentName($message);
        if (!$studentName) {
            // Try simple heuristic: phrases like "phone of Rahul"
            if (preg_match('/(?:of|for)\s+([A-Za-z][A-Za-z\s]+)/', $message, $m)) {
                $studentCandidate = trim($m[1]);
                if ($studentCandidate) {
                    $studentName = $studentCandidate;
                }
            }
        }

        // No clear student context -> let other handlers work
        if (!$studentName) {
            return null;
        }

        // Resolve student
        $student = $this->dbHelper->getStudentByName($studentName);
        if (!$student) {
            return [
                'status' => 'success',
                'response' => "No student found with name: " . htmlspecialchars($studentName),
                'source' => 'static'
            ];
        }
        $data = $this->buildStudentData($student);

        // Synonym sets
        $phoneSyn = ['phone','phone number','contact number','mobile','mobile number'];
        $parentNameSyn = ['parent name','father name','mother name','guardian name','parents name'];
        $parentPhoneSyn = ['parent phone','parent phone number','guardian phone','father phone','mother phone'];
        $emailSyn = ['email','email address','mail id','email id'];
        $regSyn = ['registration number','reg no','reg number','roll number','rollno'];
        $balanceSyn = ['pending fees','fee balance','fee bal','balance amount','due amount','outstanding fees'];

        $in = fn(array $syn) => $this->containsAny($text, $syn);

        if ($in($phoneSyn)) {
            return [
                'status' => 'success',
                'response' => "📞 Phone of {$data['student_name']}: " . ($data['phone'] ?? 'N/A'),
                'source' => 'static'
            ];
        }
        if ($in($parentNameSyn)) {
            return [
                'status' => 'success',
                'response' => "👪 Parent/Guardian of {$data['student_name']}: " . ($data['parent_name'] ?? 'N/A'),
                'source' => 'static'
            ];
        }
        if ($in($parentPhoneSyn)) {
            return [
                'status' => 'success',
                'response' => "📞 Parent/Guardian phone for {$data['student_name']}: " . ($data['parent_phone'] ?? 'N/A'),
                'source' => 'static'
            ];
        }
        if ($in($emailSyn)) {
            return [
                'status' => 'success',
                'response' => "✉️ Email of {$data['student_name']}: " . ($data['email'] ?? 'N/A'),
                'source' => 'static'
            ];
        }
        if ($in($regSyn)) {
            return [
                'status' => 'success',
                'response' => "🆔 Registration number of {$data['student_name']}: " . ($data['reg_no'] ?? 'N/A'),
                'source' => 'static'
            ];
        }
        if ($in($balanceSyn)) {
            $amount = number_format((float)($data['pending_amount'] ?? 0), 2);
            return [
                'status' => 'success',
                'response' => "💰 Pending fees for {$data['student_name']}: ₹{$amount} (" . ($data['fee_status'] ?? 'N/A') . ")",
                'source' => 'static'
            ];
        }

        return null;
    }

    private function containsAny(string $text, array $synonyms): bool {
        foreach ($synonyms as $s) {
            if (str_contains($text, strtolower($s))) return true;
        }
        return false;
    }

    /**
     * Append recent conversation context from session for better AI continuity.
     *
     * @return string
     */
    private function buildConversationContext(): string {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['chat_history']) && is_array($_SESSION['chat_history'])) {
            $items = array_slice($_SESSION['chat_history'], -10);
            $lines = ["Recent Conversation:"];
            foreach ($items as $it) {
                $role = ($it['role'] ?? '') === 'assistant' ? 'Assistant' : 'User';
                $content = substr((string)($it['content'] ?? ''), 0, 200);
                $lines[] = "- {$role}: {$content}";
            }
            return implode("\n", $lines) . "\n";
        }
        return "";
    }

    /**
     * Return a formatted error response.
     *
     * @param string $message
     * @return array{status:string,response:string,source:string}
     */
    private function error(string $message): array {
        return [
            'status' => 'error',
            'response' => $message,
            'source' => 'error'
        ];
    }
}
?>


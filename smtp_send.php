<?php

/**
 * `smtp_send` sends mails directly via SMTP. It uses `STARTTLS` when the server
 * supports it. More examples are further down. Basic usage:
 * 
 *     $message = <<<EOD
 *     From: "Mr. Sender" <sender@dep1.example.com>
 *     To: "Mr. Receiver" <receiver@dep2.example.com>
 *     Subject: SMTP Test
 *     Date: Thu, 21 Dec 2017 16:01:07 +0200
 *     Content-Type: text/plain; charset=utf-8
 *     
 *     Hello there. Just a small test. 😊
 *     End of message.
 *     EOD;
 *     smtp_send('sender@dep1.example.com', 'receiver@dep2.example.com', $message, 'mail.dep1.example.com', 587, array(
 *         'user' => 'sender',
 *         'pass' => 'secret'
 *     ));
 * 
 * 
 * Signature and parameters
 * ------------------------
 * 
 * ~~~ php
 * bool smtp_send($from, $to, $message, $smtp_server, $smtp_port, $options = array())
 * ~~~
 * 
 * __$from__: The mail address of the sender. This is given directly to the SMTP
 *   server and is independent of the `From` header in the message.
 *   
 *   If an addresses contains unquoted ">" characters the function aborts and
 *   returns `false`. Otherwise an address could be used ot inject further SMTP
 *   commands into the connection. A properly quoted address looks like this:
 *   `"foo\>bar"@example.com` (I know it looks strange but that's the RFC).
 * 
 * __$to__: The mail address of the receiver(s) given to the SMTP server. Again
 *   this is independent from the `To` header(s) in the message. If an array of
 *   mail addresses is given the mail is send to each address (one `RCPT TO`
 *   command is send for each address). Just like in `$from` ">" characters have
 *   to be quoted or the function returns `false`.
 *   
 *   You can send mails to many people without giving away everyones mail
 *   address (BCC, Blind Carbon Copy). For that set the `To` header _within_ the
 *   mail to something like "Undisclosed recipients:;" while listing all
 *   addresses in the `$to` parameter. Then the SMTP server will send the mail
 *   to everyone but the mail doesn’t contain their addresses (example further
 *   down).
 * 
 * __$message__: The source code of the mail. You can find examples below or in
 *   [RFC5322 appendix A][1]. `\n` line breaks are automatically
 *   converted to `\r\n` (CRLF) so you can just use normal line breaks when
 *   constructing it.
 * 
 * [1]: https://tools.ietf.org/html/rfc5322#appendix-A
 * 
 * __$smtp_server__ and __$smtp_port__: The hostname (or IP address) and port of
 *   the SMTP server. That’s the server you submit the mail to and it’ll figure
 *   out how it gets there. The function will use [`fsockopen()`][2] to connect
 *   to that server and submit the mail via SMTP commands. Since `fsockopen()`
 *   supports [transports][3] you can use TLS or UNIX domain sockets for
 *   submission (again, see code examples further down).
 *   
 *   Example hostnames: `127.0.0.1`, `::1`, `mail.example.com`,
 *   `tls://mail.example.com`
 *   
 *   Often used ports:
 *   
 *   - 587 (starts without encryption but usually secured with `STARTTLS`)
 *   - 465 (server expects connection to use the TLS transport)
 *   - 25 (usually for communication between mail servers)
 * 
 * [2]: http://php.net/fsockopen
 * [3]: http://php.net/transports
 * 
 * __$options__: An array with further arguments that are only needed for
 *   extended functionality like SMTP authentication or some down to the wire
 *   details.
 *   
 *   - `user` and `pass` keys: SMTP user name and password. Both have to be set
 *     to enable authentication. Right now only PLAIN authentication is
 *     supported.  
 *     Default when not specified: Don't do SMTP authentication.
 *   - `timeout`: The timeout in seconds used by `fsockopen()`. If it can't
 *     connect to the SMTP server in that time the function aborts.  
 *     Default when not specified: Return value of `ini_get("default_socket_timeout")`.
 *   - `client_domain`: The client identification send to the server with the
 *     `EHLO` greeting. Looks like servers ignore it. I don’t know what they
 *     should do with it.  
 *     Default when not specified: Return value of `gethostname()`.
 * 
 * Return value
 * ------------
 * 
 * The function returns `true` if the mail was submitted, `false` if an error
 * occurred. No detailed error messages are supported but feel free to add it to
 * the code if needed.
 * 
 * Code examples
 * -------------
 * 
 * Sending a text mail via port 587 with SMTP authentication. `STARTTLS` is
 * automatically used when the server supports it. Otherwise the connection will
 * be unencrypted.
 * 
 *     $message = <<<EOD
 *     From: "Mr. Sender" <sender@example.com>
 *     To: "Mr. Receiver" <receiver@example.com>
 *     Subject: SMTP Test
 *     Date: Thu, 21 Dec 2017 16:01:07 +0200
 *     Content-Type: text/plain; charset=utf-8
 *     
 *     Hello there. Just a small test. 😊
 *     End of message.
 *     EOD;
 *     smtp_send('sender@example.com', 'receiver@example.com', $message, 'mail.example.com', 587, array(
 *         'user' => 'sender',
 *         'pass' => 'secret'
 *     ));
 * 
 * * * *
 * 
 * Sending a text mail via port 465. The server expects an encrypted connection
 * so we have to use the TLS transport (notice the `tls://` at the start of the
 * hostname).
 * 
 *     $message = <<<EOD
 *     From: "Mr. Sender" <sender@example.com>
 *     To: "Mr. Receiver" <receiver@example.com>
 *     Subject: SMTP Test
 *     Date: Thu, 21 Dec 2017 16:01:07 +0200
 *     Content-Type: text/plain; charset=utf-8
 *     
 *     Hello there. Just a small test. 😊
 *     End of message.
 *     EOD;
 *     smtp_send('sender@example.com', 'receiver@example.com', $message, 'tls://mail.example.com', 465, array(
 *         'user' => 'sender',
 *         'pass' => 'secret'
 *     ));
 * 
 * * * *
 * 
 * Sending a mail to many recipients without giving away their addresses to
 * everyone (BCC, Blind Carbon Copy):
 * 
 *     $message = <<<EOD
 *     From: "Mr. Sender" <sender@example.com>
 *     To: Undisclosed recipients:;
 *     Subject: SMTP BCC Test
 *     Date: Thu, 21 Dec 2017 16:01:07 +0200
 *     Content-Type: text/plain; charset=utf-8
 *     
 *     Try to figure out who else got this message…
 *     EOD;
 *     smtp_send('sender@example.com', [
 *         'smith@example.com', 'jones@example.com', 'taylor@example.com'
 *     ], $message, 'mail.example.com', 587, array(
 *         'user' => 'sender',
 *         'pass' => 'secret'
 *     ));
 * 
 * * * *
 * 
 * Sending an HTML mail:
 * 
 *     $message = <<<EOD
 *     From: "Mr. Sender" <sender@example.com>
 *     To: "Mr. Receiver" <receiver@example.com>
 *     Subject: SMTP Test
 *     Date: Thu, 21 Dec 2017 16:01:07 +0200
 *     MIME-Version: 1.0
 *     Content-Type: text/html; charset=utf-8
 *     
 *     <html>
 *     <head><title>An HTML mail</title></head>
 *     <body>
 *         <h1>See, HTML!</h1>
 *         <p>Have fun 😊</p>
 *     </body>
 *     </html>
 *     EOD;
 *     smtp_send('sender@example.com', 'receiver@example.com', $message, 'mail.example.com', 587, array(
 *         'user' => 'sender',
 *         'pass' => 'secret'
 *     ));
 * 
 * * * *
 * 
 * Sending a mail with an attachment ("mixed content"):
 * 
 *     $base64_file_data = base64_encode(file_get_contents("file.xyz"));
 *     $message = <<<EOD
 *     From: "Mr. Sender" <sender@example.com>
 *     To: "Mr. Receiver" <receiver@example.com>
 *     Subject: SMTP Attachment Test
 *     Date: Thu, 21 Dec 2017 16:01:07 +0200
 *     MIME-Version: 1.0
 *     Content-Type: multipart/mixed; boundary="XXXXXXXXXX"
 *     
 *     This is the preamble. It's ignored.
 *     --XXXXXXXXXX
 *     Content-Type: text/plain; charset=utf-8
 *     
 *     This is a mail with an attachment. Have fun with it.
 *     --XXXXXXXXXX
 *     Content-Type: application/octet-stream; name="file.xyz"
 *     Content-Transfer-Encoding: base64
 *     Content-Disposition: attachment; filename="file.xyz"
 *     
 *     $base64_file_data
 *     --XXXXXXXXXX--
 *     This is the epilogue. It's also ignored.
 *     EOD;
 *     smtp_send('sender@example.com', 'receiver@example.com', $message, 'mail.example.com', 587, array(
 *         'user' => 'sender',
 *         'pass' => 'secret'
 *     ));
 * 
 * Based on the example in [RFC 2046 page 21][4]. The line "--XXXXXXXXXX" is the
 * boundary between the different parts and must not occur in any of them.
 * "--XXXXXXXXXX--" (note "--" at the end) signals the end of the last part.
 * Everything after that is ignored. Usually the boundary is a random
 * alphanumeric string with 20 to 30 characters. I just chose "XXXXXXXXXX" for
 * better visibility.
 * 
 * [4]: https://tools.ietf.org/html/rfc2046#page-21
 * 
 * Some mail clients use the filename in Content-Type name, some the one in
 * Content-Disposition filename. So set both.
 * 
 * Please set the Content-Type of the attachment to a proper value (e.g.
 * "image/jpeg" for *.jpg files). This helps mail clients to select a proper
 * program for opening the attachment. "application/octet-stream" is the MIME
 * type for a binary file with unknown content.
 * 
 * You can also use "Content-Disposition: inline" instead of attachment. In that
 * case the browser tries to show the attachment as part of the message. This is
 * often used for images.
 * 
 * * * *
 * 
 * Sending a mail with the same content as text and HTML (alternate content):
 * 
 *     $message = <<<EOD
 *     From: "Mr. Sender" <sender@example.com>
 *     To: "Mr. Receiver" <receiver@example.com>
 *     Subject: SMTP Alternate Content Test
 *     Date: Thu, 21 Dec 2017 16:01:07 +0200
 *     MIME-Version: 1.0
 *     Content-Type: multipart/alternative; boundary="XXXXXXXXXX"
 *     
 *     This is the preamble. It's ignored.
 *     --XXXXXXXXXX
 *     Content-Type: text/plain; charset=utf-8
 *     
 *     A message with a list:
 *     
 *     - Point A
 *     - Point B
 *     - Point C
 *     
 *     --XXXXXXXXXX
 *     Content-Type: text/html; charset=utf-8
 *     
 *     <html>
 *     <head><title>SMTP Alternate Content Test</title></head>
 *     <body>
 *         <p>A message with a list:</p>
 *         <ul>
 *             <li>Point A</li>
 *             <li>Point B</li>
 *             <li>Point C</li>
 *         </ul>
 *     </body>
 *     </html>
 *     --XXXXXXXXXX--
 *     This is the epilogue. It's also ignored.
 *     EOD;
 *     smtp_send('sender@example.com', 'receiver@example.com', $message, 'mail.example.com', 587, array(
 *         'user' => 'sender',
 *         'pass' => 'secret'
 *     ));
 * 
 * * * *
 * 
 * You can also nest mixed and alternative content. For example to send a
 * message with an attachment where the text part is provided as plain text and
 * HTML.
 * 
 *     $base64_file_data = base64_encode(file_get_contents("file.xyz"));
 *     $message = <<<EOD
 *     From: "Mr. Sender" <sender@example.com>
 *     To: "Mr. Receiver" <receiver@example.com>
 *     Subject: SMTP Alternate and Mixed Content Test
 *     Date: Thu, 21 Dec 2017 16:01:07 +0200
 *     MIME-Version: 1.0
 *     Content-Type: multipart/mixed; boundary="XXXXXXXXXX"
 *     
 *     --XXXXXXXXXX
 *     Content-Type: multipart/alternative; boundary="YYYYYYYYYY"
 *     
 *     --YYYYYYYYYY
 *     Content-Type: text/plain; charset=utf-8
 *     
 *     A message with a list:
 *     
 *     - Point A
 *     - Point B
 *     - Point C
 *     
 *     --YYYYYYYYYY
 *     Content-Type: text/html; charset=utf-8
 *     
 *     <html>
 *     <head><title>SMTP Alternate and Mixed Content Test</title></head>
 *     <body>
 *         <p>A message with a list:</p>
 *         <ul>
 *             <li>Point A</li>
 *             <li>Point B</li>
 *             <li>Point C</li>
 *         </ul>
 *     </body>
 *     </html>
 *     --YYYYYYYYYY--
 *     --XXXXXXXXXX
 *     Content-Type: application/octet-stream; name="file.xyz"
 *     Content-Transfer-Encoding: base64
 *     Content-Disposition: attachment; filename="file.xyz"
 *     
 *     $base64_file_data
 *     --XXXXXXXXXX--
 *     EOD;
 *     smtp_send('sender@example.com', 'receiver@example.com', $message, 'mail.example.com', 587, array(
 *         'user' => 'sender',
 *         'pass' => 'secret'
 *     ));
 * 
 * 
 * License
 * -------
 * 
 * `smtp_send()` function © 2018 Stephan Soller  
 * Distributed under the MIT License
 * 
 * Version history
 * ---------------
 * 
 * - 2018-04-24 by Stephan Soller <stephan.soller@helionweb.de>  
 *   Added code to reject mail addresses that could potentially inject other
 *   SMTP commands.
 * 
 * - 2018-03-12 by Stephan Soller <stephan.soller@helionweb.de>  
 *   Multiple greeting lines from the server were not correctly consumed. This
 *   prevented mail submission on some SMTP servers.
 * 
 * - 2014-09-14  by Stephan Soller <stephan.soller@helionweb.de>  
 *   Wrote function to be independent of PHPs mail configuration.
 */
function smtp_send($from, $to, $message, $smtp_server, $smtp_port, $options = array()) {
	// Set default values
	if ( ! isset($options['timeout']) )
		$options['timeout'] = ini_get("default_socket_timeout");
	if ( ! isset($options['client_domain']) )
		$options['client_domain'] = gethostname();
	
	// Sanitize parameters
	if ( ! is_array($to) )
		$to = array($to);
	
	// Later on we'll use the addresses in commands like "MAIL FROM:<$from>" and
	// "RCPT TO:<$to>". If an address contains unescaped ">" characters it might
	// break out of the command (like an SQL injection attack but with SMPT). To
	// make sure this is not used to attack an SMPT server we only accept
	// addresses without ">" chars or where it's properly quoted.
	$is_address_safe = function($address){
		// If the address doesn't contain a ">" character it's safe
		if ( strpos($address, ">") === false )
			return true;
		// Address only contains properly quoted ">" characters so it's safe
		// Example (the quote are part of it): "foo\>bar\"batz"@example.com
		// See https://tools.ietf.org/html/rfc5321#page-42 and
		// https://stackoverflow.com/a/201378
		if ( preg_match('/^"(?:\\.|[^\\])*"@[^@>]+$/', $address) )
			return true;
		// Address contains unescaped ">" characters so it'll break commands.
		return false;
	};
	
	if ( !$is_address_safe($from) )
		return false;
	foreach($to as $recipient) {
		if ( !$is_address_safe($recipient) )
			return false;
	}
	
	// Small helper function to send SMTP commands and receive their responses.
	// If `$command_line` is `null? nothing is send and any pending responses
	// are collected (useful to consume the greeting lines).
	// See http://tools.ietf.org/html/rfc5321#section-4.1.1
	$command = function($command_line) use(&$con) {
		if ($command_line !== null)
			fwrite($con, "$command_line\r\n");
		
		$status = null;
		$text = array();
		while( $line = fgets($con) ) {
			$status = substr($line, 0, 3);
			$text[] = trim(substr($line, 4));
			if (substr($line, 3, 1) === ' ')
				break;
		}
		
		return array($status, $text);
	};
	
	// Connect to SMTP server
	$con = fsockopen($smtp_server, @$smtp_port, $errno, $errstr, $options['timeout']);
	if ($con === false)
		return false;
	
	// Consume the greeting line(s)
	list($status, $greetings) = $command(null);
	if ($status != 220) {
		fclose($con);
		return false;
	}
	
	// Say hello to the server
	// See http://tools.ietf.org/html/rfc5321#section-4.1.1.1
	list($status, $capabilities) = $command('EHLO ' . $options['client_domain']);
	if ($status != 250) {
		fclose($con);
		return false;
	}
	
	// Try TLS if available
	// See http://tools.ietf.org/html/rfc3207
	if (in_array('STARTTLS', $capabilities)) {
		list($status, ) = $command('STARTTLS');
		if ($status == 220) {
			if ( ! stream_socket_enable_crypto($con, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) ) {
				$command('QUIT');
				fclose($con);
				return false;
			}
			
			list($status, $capabilities) = $command('EHLO ' . $options['client_domain']);
			if ($status != 250) {
				$command('QUIT');
				fclose($con);
				return false;
			}
		}
	}
	
	// Authenticate using PLAIN method if we have credentials
	// See http://tools.ietf.org/html/rfc4954#section-4
	if ( isset($options['user']) and isset($options['user']) ) {
		list($status, ) = $command('AUTH PLAIN ' . base64_encode("\0" . $options['user'] . "\0" . $options['pass']));
		if ($status != 235) {
			$command('QUIT');
			fclose($con);
			return false;
		}
	}
	
	// Submit the mail. We do no individual error checking here because errors
	// will propagate and cause the last command to fail.
	$command('MAIL FROM:<' . $from . '>');
	foreach($to as $recipient)
		$command('RCPT TO:<' . $recipient . '>');
	$command('DATA');
	
	// Convert all line breaks in the message to \r\n and escape leading dots
	// (data end signal, see http://tools.ietf.org/html/rfc5321#section-4.5.2).
	$message = preg_replace('/\r?\n/', "\r\n", $message);
	$message = preg_replace('/^\./m', '..', $message);
	// Make sure the message has a trailing line break. Otherwise the data end
	// command (.) would not work properly.
	if (substr($message, -2) !== "\r\n")
		$message .= "\r\n";
	
	fwrite($con, $message);
	list($status, ) = $command(".");
	$submission_successful = ($status == 250);
	
	$command('QUIT');
	fclose($con);
	return $submission_successful;
}

?>
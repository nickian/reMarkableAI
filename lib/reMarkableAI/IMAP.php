<?php
/**
 * IMAP functions wrapper to pull email content for prompts.
 *
 * @package reMarkableAI/IMAP
 * @version 1.0
 * @license MIT
 */

namespace reMarkableAI;

class IMAP {

    /**
     * @var array Configuration values from config.php
     */
    private $config;


    /**
     * 
     */
    /**
     * @var object When an IMAP connection is opened it's passed to this property
     */
    private $connection;


    /**
     * @var object HTMLExtract class object
     */
    public $html_extract;


    /**
     * Create a connection to the IMAP server
     *
     * @param array $config Configuration values from config.php
     */
    public function __construct($config, $html_extract)
    {
        $this->config = $config;
        $this->html_extract = $html_extract;
    }


    /**
     * Create a connection to the IMAP server and set the $connection property
     *
     * @param array $config_imap Configuration values from config.php
     */
    public function connectImap($config_imap)
    {
        $imap_server = '{'.$config_imap['server'].':'.$config_imap['port'].'/imap/'.$config_imap['encryption'].'}'.$config_imap['folder'];
        $this->connection = imap_open(
            $imap_server, 
            $config_imap['user'], 
            $config_imap['password']
        ) or die('Unable to connect to IMAP server: ' . imap_last_error());
    }


    /**
     * Mark an email as "seen"/"read" on the IMAP server.
     *
     * @param integer $message_number The message number
     */
    public function markEmailRead($message_number)
    {
        $this->connectImap($this->config['imap']);
        imap_setflag_full($this->connection, $message_number, "\\Seen");
        imap_close($this->connection);
    }


    /**
     * Delete an email on the IMAP server.
     *
     * @param integer $message_number The message number
     */
    public function deleteEmail($message_number)
    {
        $this->connectImap($this->config['imap']);
        // Mark the message for deletion
        imap_delete($this->connection, $message_number);
        // Permanently remove all messages marked for deletion
        imap_expunge($this->connection);
        imap_close($this->connection);
    }
    

    /**
     * Check if a string exists inside of another string
     *
     * @param string $string The string to check for a value in
     * @param string $substring The substring to look for
     * 
     * @return boolean
     */
    function containsSubstring($string, $substring) {
        $position = strpos($string, $substring);
        return $position !== false;
    }


    /**
     * Get a list of unread emails where the subject line contains our prompt keyword.
     *
     * @param integer $message_number The message number
     * 
     * @return array $unread_prompt_emails List of email messages
     */
    public function getUnreadPromptEmails()
    {
        $unread_prompt_emails = [];
        $unread_emails = $this->getUnreadEmails();
        foreach( $unread_emails as $unread_email ) {
            if ( $this->containsSubstring($unread_email['subject'], $this->config['prompt_keyword'])  ) {
                $unread_prompt_emails[] = $unread_email;
                $message_no = $unread_email['header']->Msgno;
                if ( $this->config['imap']['delete'] ) {
                    $this->deleteEmail($message_no);
                } elseif ( $this->config['imap']['mark_read'] ) {
                    $this->markEmailRead($message_no);
                }
            }
        }
        return $unread_prompt_emails;
    }


    /**
     * Parse a list of emails from getEmailMessages() and filter for unread messages.
     *
     * @return array $unread_messages List of email messages
     */
    public function getUnreadEmails()
    {
        $messages = $this->getEmailMessages();
        $unread_messages = [];
        foreach( $messages as $message ) {
            if ( !$message['read'] ) {
                $unread_messages[] = $message;
            }
        }
        return $unread_messages;
    }


    /**
     * Connect to an IMAP inbox and get messages from our folder specified in config.php
     *
     * @return array $messages List of email messages
     */
    public function getEmailMessages()
    {
        $this->connectImap($this->config['imap']);
        $messages = [];
        $num_messages = imap_num_msg($this->connection);

        for ($i = 1; $i <= $num_messages; $i++) {
            $header_info = imap_headerinfo($this->connection, $i);
            $read = ($header_info->Unseen == "U") ? false : true;
            $body = imap_fetchbody($this->connection, $i, 1, FT_PEEK);
            $structure = imap_fetchstructure($this->connection, $i);
            if ($structure->encoding == 3) {
                $body = imap_base64($body);
            } elseif ($structure->encoding == 4) {
                $body = imap_qprint($body);
            }
            // Extract content within <p> tags
            $p_tags = $this->html_extract->extractPTagsContent($body);
            $messages[] = [
                'read' => $read,
                'header' => $header_info,
                'subject' => $header_info->subject,
                'from' => $header_info->fromaddress,
                'body' => $body,
                'paragraphs' => $p_tags
            ];
        }
        imap_close($this->connection);
        return $messages;
    }
}
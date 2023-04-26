<?php
require_once('vendor/autoload.php');
require_once('config.php');

$cli = new League\CLImate\CLImate;

// Define argument to specify a cron job
$cli->arguments->add([
    'cron' => [
        'prefix'       => 'c',
        'longPrefix'   => 'cron',
        'description'  => 'Use in cron, not interactive.',
        'defaultValue' => true,
    ]
]);

if ( !file_exists(__DIR__.'/config.php') ) {
    $cli->red('Create your config.php file first.');
    die();
}

$remarkable_ai = new reMarkableAI\reMarkable($config);

// Parse arguments
$cli->arguments->parse();

// This is a cron job
if ( $cli->arguments->defined('cron') ) {

    $prompts = $remarkable_ai->getPrompts();

    // Found prompt emails
    if ( count($prompts) > 0 ) {
        
        $cli->green('Found '.count($prompts).' prompts.');
    
        $i = 1;
        
        foreach($prompts as $prompt) {
    
            $cli->out('<green>Prompt '.$i.':</green> '.$prompt);
    
            // Get AI Respose for prompt
            $cli->magenta('Getting response from OpenAI...');
            $response = $remarkable_ai->getAIResponse($prompt);
    
            if ( $response['headline'] && $response['body'] ) {
                $cli->out('<green>Response received:</green> "'.$response['headline'].'"');
            } else {
                $cli->red('Unable get response.');
                die();
            }
    
            // Create a PDF document from the response.
            $cli->out('<magenta>Creating PDF Document...</magenta>');
            $pdf_file = $remarkable_ai->createResponsePdf($response['headline'], $response['body']);
    
            if ( file_exists(__DIR__.'/documents/'.$pdf_file) ) {
                $cli->out('<green>Document created:</green> '.$pdf_file);
            } else {
                $cli->red('Unable to create PDF document.');
                die();
            }
    
            // Send to reMarkable Cloud
            $cli->out('<magenta>Sending to reMarkable cloud:</magenta> '.$pdf_file);
            $remarkable_ai->sendToRMAPI($pdf_file);
    
            $i++;
    
        }
        
    }

// This is an interactive prompt
} else {

    $input = $cli->input('<green>Enter a prompt:</green> ');
    $prompt = $input->prompt();
    
    // Get AI Respose for prompt
    $cli->magenta('Getting response from OpenAI...');
    $response = $remarkable_ai->getAIResponse($prompt);

    if ( $response['headline'] && $response['body'] ) {
        $cli->out('<green>Response received:</green> "'.$response['headline'].'"');
    } else {
        $cli->red('Unable get response.');
        die();
    }

    // Create a PDF document from the response.
    $cli->out('<magenta>Creating PDF Document...</magenta>');
    $pdf_file = $remarkable_ai->createResponsePdf($response['headline'], $response['body']);

    if ( file_exists(__DIR__.'/documents/'.$pdf_file) ) {
        $cli->out('<green>Document created:</green> '.$pdf_file);
    } else {
        $cli->red('Unable to create PDF document.');
        die();
    }

    // Send to reMarkable Cloud
    $cli->out('<magenta>Sending to reMarkable cloud:</magenta> '.$pdf_file);
    $remarkable_ai->sendToRMAPI($pdf_file);

}


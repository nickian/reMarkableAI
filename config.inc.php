<?php

// Keyword to look for in email subjects lines.
$config = [
    'script_path' => __DIR__,
    'prompt_keyword' => 'Prompt',
    // If combine_paragraphs is set to true, all paragraphs in a written page will be combined into one prompt.
    // If combine_paragraphs is set to false, each handwritten paragraph will be a separate prompt and produce a separate response document.
    'combine_paragraphs' => false,
    // We append this to the end of each prompt you write to make it easier to parse the response.
    'prompt_append' => ' Output your paragraphs in HTML <p> tags. Create a headline in an <h1> tag at the beginning.'
];

// OpenAI API Configuration
$config['openai'] = [
    // Get an API Key here: https://platform.openai.com/account/api-keys
    'key' => '',
    'options' => [
        'model' => 'text-davinci-003',
        'temperature' => 0.5,
        'max_tokens' => 2500,
        'frequency_penalty' => 0.5,
        'presence_penalty' => 0.5,
        'stream' => false,
        'n' => 1
    ]
];

// IMAP Server Configuration
$config['imap'] = [
    'server' => '',
    'port' => 993,
    'encryption' => 'ssl',
    'user' => '',
    'password' => '',
    // The folder in your inbox to look for prompts.
    'folder' => 'Inbox',
    // Careful. If you set mark_read to false and you're running a cron job, a new response is created over 
    // and over again for your same prompts. Setting this to false is for testing purposes.
    'mark_read' => true,
    // Whether or not we should permanently delete the message after it is read.
    'delete' => false
];

// RMAPI Configuration
$config['rmapi'] = [
    // The folder on your reMarkable cloud to upload documents into.
    'folder' => '/'
];

// mPDF Style Configuration
$config['pdf'] = [
    'stylesheet' => file_get_contents(__DIR__.'/css/pdf_stylesheet.css')
];
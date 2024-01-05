<p align="center">
    <img src="https://raw.githubusercontent.com/gemini-api-php/client/main/assets/example.png" width="800" alt="Gemini API PHP Client - Example">
</p>
<p align="center">
    <a href="https://packagist.org/packages/gemini-api-php/client"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/gemini-api-php/client"></a>
    <a href="https://packagist.org/packages/gemini-api-php/client"><img alt="Latest Version" src="https://img.shields.io/packagist/v/gemini-api-php/client"></a>
    <a href="https://packagist.org/packages/gemini-api-php/client"><img alt="License" src="https://img.shields.io/github/license/gemini-api-php/client"></a>
</p>

# Gemini API PHP Client

Gemini API PHP Client allows you to use the Google's generative AI models, like Gemini Pro and Gemini Pro Vision.

_This library is not developed or endorsed by Google._

- Erdem Köse - **[github.com/erdemkose](https://github.com/erdemkose)**

## Table of Contents
- [Installation](#installation)
- [How to use](#how-to-use)
  - [Basic text generation](#basic-text-generation)
  - [Multimodal input](#multimodal-input)
  - [Chat Session (Multi-Turn Conversations)](#chat-session-multi-turn-conversations)
  - [Chat Session with history](#chat-session-with-history)
  - [Streaming responses](#streaming-responses)
  - [Streaming Chat Session](#streaming-chat-session)
  - [Tokens counting](#tokens-counting)
  - [Listing models](#listing-models)

## Installation

> You need an API key to gain access to Google's Gemini API.
> Visit [Google AI Studio](https://makersuite.google.com/) to get an API key.

First step is to install the Gemini API PHP client with Composer.

```shell
composer require gemini-api-php/client
```

Gemini API PHP client does not come with an HTTP client.
If you are just testing or do not have an HTTP client library in your project,
you need to allow `php-http/discovery` composer plugin or install a PSR-18 compatible client library.

## How to use

### Basic text generation

```php
$client = new GeminiAPI\Client('GEMINI_API_KEY');

$response = $client->geminiPro()->generateContent(
    new TextPart('PHP in less than 100 chars')
);

print $response->text();
// PHP: A server-side scripting language used to create dynamic web applications.
// Easy to learn, widely used, and open-source.
```

### Multimodal input

> Image input modality is only enabled for Gemini Pro Vision model

```php
$client = new GeminiAPI\Client('GEMINI_API_KEY');

$response = $client->geminiProVision()->generateContent(
    new TextPart('Explain what is in the image'),
    new ImagePart(
        MimeType::IMAGE_JPEG,
        base64_encode(file_get_contents('elephpant.jpg')),
    ),
);

print $response->text();
// The image shows an elephant standing on the Earth.
// The elephant is made of metal and has a glowing symbol on its forehead.
// The Earth is surrounded by a network of glowing lines.
// The image is set against a starry background.
```

### Chat Session (Multi-Turn Conversations)

```php
$client = new GeminiAPI\Client('GEMINI_API_KEY');

$chat = $client->geminiPro()->startChat();

$response = $chat->sendMessage(new TextPart('Hello World in PHP'));
print $response->text();

$response = $chat->sendMessage(new TextPart('in Go'));
print $response->text();
```

```text
<?php
echo "Hello World!";
?>

This code will print "Hello World!" to the standard output.
```

```text
package main

import "fmt"

func main() {
    fmt.Println("Hello World!")
}

This code will print "Hello World!" to the standard output.
```

### Chat Session with history

```php
$client = new GeminiAPI\Client('GEMINI_API_KEY');

$history = [
    Content::text('Hello World in PHP', Role::User),
    Content::text(
        <<<TEXT
        <?php
        echo "Hello World!";
        ?>
        
        This code will print "Hello World!" to the standard output.
        TEXT,
        Role::Model,
    ),
];
$chat = $client->geminiPro()
    ->startChat()
    ->withHistory($history);

$response = $chat->sendMessage(new TextPart('in Go'));
print $response->text();
```

```text
package main

import "fmt"

func main() {
    fmt.Println("Hello World!")
}

This code will print "Hello World!" to the standard output.
```

### Streaming responses

> Requires `curl` extension to be enabled

In the streaming response, the callback function will be called whenever a response is returned from the server.

Long responses may be broken into separate responses, and you can start receiving responses faster using a content stream.

```php
$client = new GeminiAPI\Client('GEMINI_API_KEY');

$callback = function (GenerateContentResponse $response): void {
    static $count = 0;

    print "\nResponse #{$count}\n";
    print $response->text();
    $count++;
};

$client->geminiPro()->generateContentStream(
    $callback,
    new TextPart('PHP in less than 100 chars')
);
// Response #0
// PHP: a versatile, general-purpose scripting language for web development, popular for
// Response #1
//  its simple syntax and rich library of functions.
```

### Streaming Chat Session

> Requires `curl` extension to be enabled 

```php
$client = new GeminiAPI\Client('GEMINI_API_KEY');

$history = [
    Content::text('Hello World in PHP', Role::User),
    Content::text(
        <<<TEXT
        <?php
        echo "Hello World!";
        ?>
        
        This code will print "Hello World!" to the standard output.
        TEXT,
        Role::Model,
    ),
];
$chat = $client->geminiPro()
    ->startChat()
    ->withHistory($history);

$callback = function (GenerateContentResponse $response): void {
    static $count = 0;

    print "\nResponse #{$count}\n";
    print $response->text();
    $count++;
};

$chat->sendMessageStream($callback, new TextPart('in Go'));
```

```text
Response #0
package main

import "fmt"

func main() {

Response #1
    fmt.Println("Hello World!")
}

This code will print "Hello World!" to the standard output.
```

### Embed Content

```php
$client = new GeminiAPI\Client('GEMINI_API_KEY');

$response = $client->embeddingModel(ModelName::Embedding)
    ->embedContent(
        new TextPart('PHP in less than 100 chars'),
    );

print_r($response->embedding->values);
// [
//    [0] => 0.041395925
//    [1] => -0.017692696
//    ...
// ]
```

### Tokens counting

```php
$client = new GeminiAPI\Client('GEMINI_API_KEY');

$response = $client->geminiPro()->countTokens(
    new TextPart('PHP in less than 100 chars'),
);

print $response->totalTokens;
// 10
```

### Listing models

```php
$client = new GeminiAPI\Client('GEMINI_API_KEY');

$response = $client->listModels();

print_r($response->models);
//[
//  [0] => GeminiAPI\Resources\Model Object
//    (
//      [name] => models/gemini-pro
//      [displayName] => Gemini Pro
//      [description] => The best model for scaling across a wide range of tasks
//      ...
//    )
//  [1] => GeminiAPI\Resources\Model Object
//    (
//      [name] => models/gemini-pro-vision
//      [displayName] => Gemini Pro Vision
//      [description] => The best image understanding model to handle a broad range of applications
//      ...
//    )
//]
```

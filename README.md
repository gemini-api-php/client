<p align="center">
    <img src="https://raw.githubusercontent.com/erdemkose/generative-ai-php/main/assets/example.png" width="800" alt="Generative AI PHP Client">
</p>
<p align="center">
    <a href="https://packagist.org/packages/erdemkose/generative-ai-php"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/erdemkose/generative-ai-php"></a>
    <a href="https://packagist.org/packages/erdemkose/generative-ai-php"><img alt="Latest Version" src="https://img.shields.io/packagist/v/erdemkose/generative-ai-php"></a>
    <a href="https://packagist.org/packages/erdemkose/generative-ai-php"><img alt="License" src="https://img.shields.io/github/license/erdemkose/generative-ai-php"></a>
</p>

# Generative AI PHP Client
Generative AI PHP Client allows you to use the Google's Generative AI models, like Gemini Pro and Gemini Pro Vision.

_This library is not developed or endorsed by Google._

- Erdem Köse - **[github.com/erdemkose](https://github.com/erdemkose)**

## Table of Contents
- [Installation](#installation)
- [How to use](#how-to-use)
  - [Basic text generation](#basic-text-generation)
  - [Multimodal input](#multimodal-input)
  - [Chat Session (Multi-Turn Conversations)](#chat-session-multi-turn-conversations)
  - [Tokens counting](#tokens-counting)
  - [Listing models](#listing-models)

## Installation

> You need an API key to gain access to Google Generative AI services.
> Visit [Google AI Studio](https://makersuite.google.com/) to get an API key.

First step is to install the Generative AI PHP client with Composer.

```shell
composer require erdemkose/generative-ai-php
```

Generative AI PHP client does not come with an HTTP client.
If you are just testing or do not have an HTTP client library in your project,
you need to allow `php-http/discovery` composer plugin or install a PSR-18 compatible client library.

## How to use

### Basic text generation

```php
$client = new GenerativeAI\Client('YOUR_GEMINI_PRO_API_TOKEN');

$response = $client->GeminiPro()->generateContent(
    new TextPart('PHP in less than 100 chars')
);

print $response->text();
// PHP: A server-side scripting language used to create dynamic web applications.
// Easy to learn, widely used, and open-source.
```

### Multimodal input

> Image input modality is only enabled for Gemini Pro Vision model

```php
$client = new GenerativeAI\Client('YOUR_GEMINI_PRO_API_TOKEN');

$response = $client->GeminiProVision()->generateContent(
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
$client = new GenerativeAI\Client('YOUR_GEMINI_PRO_API_TOKEN');

$chat = $client->GeminiPro()->startChat();

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

### Tokens counting

```php
$client = new GenerativeAI\Client('YOUR_GEMINI_PRO_API_TOKEN');

$response = $client->GeminiPro()->countTokens(
    new TextPart('PHP in less than 100 chars'),
);

print $response->totalTokens;
// 10
```

### Listing models

```php
$client = new GenerativeAI\Client('YOUR_GEMINI_PRO_API_TOKEN');

$response = $client->listModels();

print_r($response->models);
//[
//  [0] => GenerativeAI\Resources\Model Object
//    (
//      [name] => models/gemini-pro
//      [displayName] => Gemini Pro
//      [description] => The best model for scaling across a wide range of tasks
//      ...
//    )
//  [1] => GenerativeAI\Resources\Model Object
//    (
//      [name] => models/gemini-pro-vision
//      [displayName] => Gemini Pro Vision
//      [description] => The best image understanding model to handle a broad range of applications
//      ...
//    )
//]
```

---
title: Introduction
description: Learn what Laravel Disposable Email does, when to use it, and how it fits into real Laravel applications.
head:
  - - meta
    - name: keywords
      content: laravel disposable email introduction, disposable email package overview, laravel temporary email blocker, laravel package intro
---

# Introduction

Laravel Disposable Email is a package for one specific job: helping you reject disposable or temporary email addresses before they affect your application.

If your app depends on real users, real inboxes, or cleaner lead data, disposable email addresses quickly become a problem. They can reduce signup quality, weaken trial restrictions, create noisy CRM records, and make follow-up communication less reliable.

This package gives you a Laravel-friendly way to deal with that problem without adding complexity to your codebase.

## What the package includes

- A built-in list of more than 110,646 disposable domains
- A validation rule you can use directly in forms and Form Requests
- Runtime checking through the rule class and facade
- A Blade directive for simple conditional output
- Remote syncing for domain updates
- Support for your own blacklist files
- Optional caching for better performance

## Typical use cases

- Registration and onboarding forms
- Free trial protection
- Invite and referral flows
- Admin review tools
- Any workflow where a permanent email address matters

## How it fits into a Laravel app

In most apps, you start by adding the validation rule to your request layer:

```php
$request->validate([
    'email' => ['required', 'email', 'disposable_email'],
]);
```

If you also need checks deeper in your business logic, you can use the same package in services, actions, jobs, and controllers.

That makes the package easy to adopt. You can start small at the form layer and expand only if your workflow needs more control.

# LaravelDisposableEmail Refactor Optimization Plan

## Goal

Package ko file/folder base par chhote, focused functions/classes me organize karna hai without breaking current behavior.

Existing public API same rahegi:

- Validation rule string: `disposable_email`
- Optional validation modes: `disposable_email:rfc,dns`
- Rule class: `EragLaravelDisposableEmail\Rules\DisposableEmailRule`
- Facade methods: `Disposable::email()`, `Disposable::domain()`, `Disposable::check()`, `Disposable::rule()`, `Disposable::make()`
- Artisan commands: `erag:install-disposable-email`, `erag:sync-disposable-email-list`, `disposable:stats`
- Blade conditional: `@disposableEmail(...)`
- Config keys: `blacklist_file`, `remote_url`, `whitelist`, `block_subdomains`, `cache_enabled`, `cache_ttl`, `sync_timeout`

## Current Problem Areas

1. `DisposableEmailRule` me validation ke saath domain extraction, whitelist parsing, blacklist file loading, source map, cache lookup aur domain matching ka logic mixed hai.
2. `Sync` command me remote URL parsing, HTTP fetch, JSON/text parsing, domain normalization, filename generation aur file write sab ek class me hai.
3. `Stats` custom blacklist file reading aur domain normalization duplicate karta hai.
4. `Email` class ka naam broad hai. Isme cache helper aur built-in domain list dono responsibilities hain.
5. Domain normalization regex and `@` se domain extraction multiple files me repeat ho raha hai.

## Refactor Strategy

Refactor small phases me hoga. Har phase ke baad tests run honge, taaki breakage early catch ho.

Code aur docs sync me rahenge. Kisi behavior, config, command, public API ya feature ka change tab tak complete nahi hoga jab tak related docs update aur verify na ho jaye.

New internal files/classes ke names short aur clear rahenge:

- Prefer 1-2 words: `Checker`, `Matcher`, `SourceMap`, `Sync`.
- `DisposableEmail` jaise package-context words internal class names me repeat nahi honge.
- Existing public classes/files rename nahi honge, kyunki unse backward compatibility break ho sakti hai.

### Phase 1: Behavior Lock

Pehle current behavior ko tests se lock karna hai.

- Existing tests run karna: `composer test` ya focused `vendor/bin/pest`.
- Missing coverage add karna:
    - `DisposableEmailRule::check()` result source: `built-in`, `custom`, `whitelist`.
    - `block_subdomains` true/false matching.
    - Local blacklist file line normalization: plain domain, email-style input, blank lines, invalid lines.
    - Remote sync text and JSON input normalization.
    - `disposable:stats` custom-domain count and latest sync behavior.

Exit rule: tests pass, no production code behavior changed.

### Phase 2: Shared Domain Utilities

Add small support classes under `src/Support` or `src/Domain`.

Suggested files:

- `src/Support/Domain.php`
- `src/Support/Matcher.php`

Responsibilities:

- `Domain::extract(string $emailOrDomain): string`
- `Domain::isValid(string $domain): bool`
- `Domain::normalize(string $emailOrDomain): string`
- `Matcher::find(string $domain, array $domainMap, bool $blockSubdomains): ?string`

Then replace duplicated logic in:

- `DisposableEmailRule`
- `Sync`
- `Stats`

Exit rule: same tests pass, no public method removed.

### Phase 3: Domain Source Loading

Move blacklist, whitelist and built-in source map loading out of `DisposableEmailRule`.

Suggested files:

- `src/Support/SourceMap.php`
- `src/Support/Domain.php`

Responsibilities:

- Built-in domains from `Email::domains()`
- Custom blacklist `.txt` files from `config('disposable-email.blacklist_file')`
- Whitelist domains from `config('disposable-email.whitelist')`
- Source map format: `domain => built-in|custom|whitelist`

Keep compatibility wrappers in `DisposableEmailRule`:

- `getDefaultUnauthorizedProviders()`
- `getBuiltInProviders()`

Exit rule: rule and facade tests pass without changing package user-facing API.

### Phase 4: Cache Boundary

Make cache keys and cache behavior explicit.

Suggested file:

- `src/Support/Cache.php`

Responsibilities:

- Cache keys:
    - `erag-unauthorized-email-providers`
    - `erag-unauthorized-email-provider-sources`
- Flexible cache support for newer Laravel versions
- `clear()` method used by install and sync commands

`Email::clearCache()` can remain as a compatibility wrapper and delegate to `Cache::clear()`.

Exit rule: cache-related tests pass; commands still clear cache before writes.

### Phase 5: Thin Validation Rule

After utilities are extracted, `DisposableEmailRule` should only coordinate validation.

Target responsibilities:

- Cast/trim input.
- Reject missing `@` for validation use.
- Use `Domain`/`Checker` for disposable result.
- Call `$fail(...)` only when needed.
- Keep static helpers as compatibility API.

Possible new class:

- `src/Support/Checker.php`

Responsibilities:

- `check(string $emailOrDomain): DisposableEmailResult`
- `email(string $email): bool`
- `domain(string $domainOrEmail): bool`

Exit rule: `DisposableEmailRule` becomes small but existing static methods still work.

### Phase 6: Sync Command Extraction

Move sync command internals into dedicated services.

Suggested files:

- `src/Support/UrlList.php`
- `src/Support/ResponseParser.php`

Responsibilities:

- Normalize config `remote_url` to valid URL list.
- Parse remote response body from JSON or newline text.
- Generate safe filename from URL.
- Write sorted, unique domains to configured blacklist directory.

`Sync` should only handle console I/O and exit codes.

Exit rule: command feature tests pass; output text should not change unless tests are updated intentionally.

### Phase 7: Stats Command Extraction

Reuse `Domain` instead of reading/parsing blacklist files again.

Suggested file:

- `src/Support/Stats.php`

Responsibilities:

- Built-in count
- Custom blacklist count
- Total count
- Whitelist count
- Remote source count
- Cache status
- Subdomain blocking status
- Last synced timestamp

`Stats` should only render the table.

Exit rule: stats output remains stable.

### Phase 8: Service Provider Cleanup

Keep Laravel package wiring in `LaravelDisposableEmailServiceProvider`.

Adjust only after services exist:

- Register config merge.
- Register console commands inside console context if desired.
- Publish config.
- Bind reusable `Checker`/`Domain` services.
- Register `disposable_email` validation extension.
- Register `disposableEmail` Blade conditional.

Exit rule: service provider feature tests pass.

### Phase 9: RFC, DNS and Email Validation Modes

Laravel ke native email validation modes ko disposable check ke saath optional feature ke roop me add karna hai.

Supported syntax:

```php
// Existing behavior, without extra format or DNS validation.
$defaultRules = ['disposable_email'];

// Any one supported mode.
$rfcRules = ['disposable_email:rfc'];
$dnsRules = ['disposable_email:dns'];
$strictRules = ['disposable_email:strict'];
$spoofRules = ['disposable_email:spoof'];
$filterRules = ['disposable_email:filter'];
$unicodeRules = ['disposable_email:filter_unicode'];

// Any supported modes can be combined with commas.
$rfcAndDnsRules = ['disposable_email:rfc,dns'];
$combinedRules = ['disposable_email:rfc,dns,spoof'];

// Form validation example.
$formRules = [
    'email' => ['required', 'disposable_email:rfc,dns'],
];
```

Syntax rule: colon ke baad ek ya multiple supported options aa sakte hain. Multiple options comma-separated honge, jaise `:rfc`, `:dns`, `:rfc,dns` or `:rfc,dns,spoof`. Supported modes kisi valid combination me use ho sakte hain.

Supported modes:

- `rfc`: supported RFCs ke according email format validate karega.
- `strict`: RFC warnings ko bhi validation failure treat karega.
- `dns`: email domain ka valid MX record check karega.
- `spoof`: homograph/deceptive Unicode address reject karega.
- `filter`: PHP `filter_var` validation use karega.
- `filter_unicode`: Unicode support ke saath PHP `filter_var` validation use karega.

Suggested short support files:

- `src/Support/Modes.php`
- `src/Support/Format.php`

Responsibilities:

- `Modes` colon/comma parameters parse karke allowed mode names normalize, deduplicate and validate karega.
- Single mode and multiple comma-separated modes dono same validation pipeline use karenge.
- `Format` Laravel ke native `email:<modes>` validator ko call karega; RFC/DNS logic duplicate nahi karega.
- Service provider `disposable_email` rule parameters ko `DisposableEmailRule` tak pass karega.
- Rule pehle requested email modes validate karega, phir whitelist/disposable-domain check karega.
- Unknown mode predictable validation/configuration error dega; silently ignore nahi hoga.

Backward compatibility:

- Plain `disposable_email` ka existing behavior and error messages same rahenge.
- RFC/DNS checks sirf explicit modes dene par run honge; DNS default me off rahega.
- Existing recommended usage `'email' => ['email:rfc,dns', 'disposable_email']` bhi work karti rahegi.
- Whitelist sirf disposable-domain block bypass karegi; invalid RFC/DNS email ko pass nahi karegi.
- `Disposable::email()`, `domain()` and `check()` default me network/DNS request nahi karenge.
- Rule-object/fluent API add karne par existing constructor, `rule()` and `make()` calls unchanged rahenge.

Tests:

- No-mode rule ka current behavior regression test.
- Har mode ka single-option test: `:rfc`, `:strict`, `:dns`, `:spoof`, `:filter`, `:filter_unicode`.
- Combined mode parsing: `:rfc,dns`, three-mode combinations, different order, duplicates and whitespace.
- Invalid RFC/filter email rejection.
- Valid and invalid DNS outcomes through a fakeable `Format` boundary, plus one environment-supported integration test.
- Unknown mode handling.
- Whitelisted but malformed/DNS-invalid email rejection.
- `dns` and `spoof` ke PHP `intl` requirement ka clear failure/troubleshooting behavior.

Feature documentation:

- Dedicated file: `/Users/zonvoir_minimac/www/vue/docs/laravel-disposable-email/docs/advanced/rfc-dns.md`
- Page title: `RFC / DNS Validation`
- Page me single modes, combined modes, all supported options, `intl` requirement, DNS/network caveat, errors and examples honge.
- `validation-and-runtime.md` me sirf short introduction aur dedicated RFC/DNS page ka link hoga.
- VitePress sidebar me separate `Advanced` group ke andar `RFC / DNS Validation` item add hoga.

Exit rule: existing tests pass, new mode tests pass, plain rule remains backward compatible, and feature docs are updated.

### Phase 10: Documentation Sync

Documentation project paths:

- Docs project root: `/Users/zonvoir_minimac/www/vue/docs/laravel-disposable-email`
- Markdown docs: `/Users/zonvoir_minimac/www/vue/docs/laravel-disposable-email/docs`
- VitePress config: `/Users/zonvoir_minimac/www/vue/docs/laravel-disposable-email/docs/.vitepress/config.mts`
- Package se relative docs path: `../docs/laravel-disposable-email/docs`

Har implementation phase me related docs saath update karne hain:

- Installation/public API: `getting-started.md`, `introduction.md`, `index.md`
- Config keys/defaults: `configuration.md`
- Validation rule/form usage: `validation/*.md`
- Facade, result object or Blade behavior: `runtime/*.md`
- RFC/DNS modes, requirements and examples: `advanced/rfc-dns.md`
- Remote sync, blacklist parsing, filenames or stats command: `domains/*.md`
- Cache keys/behavior/clearing: `advanced/cache.md`
- Scheduler behavior: `advanced/schedule.md`
- Errors, edge cases and recovery steps: `help/*.md`
- Internal architecture or maintainer workflow: `maintainers/*.md`
- New docs page: `.vitepress/config.mts` navigation/sidebar bhi update karna

### Existing Docs Split and Optimization

Current combined pages ko feature-wise split karna hai. Content copy-paste duplicate nahi hoga; har topic ki ek canonical file hogi.

Migration map:

- `validation-and-runtime.md` se validation content `validation/` aur runtime content `runtime/` files me move hoga.
- `sync-and-blacklist.md` se sync, custom blacklist, whitelist, subdomain and stats content `domains/` files me move hoga.
- `schedule-syncing-automatically.md` se application scheduling `advanced/schedule.md` me move hoga.
- `troubleshooting.md` ko validation, sync/cache and setup/Blade help pages me split karna hai.
- `caching.md` ka canonical content `advanced/cache.md` me move hoga.
- `contributing.md` ka canonical content `maintainers/contributing.md` me move hoga.
- `deprecated-5-0-0.md` version-specific migration page rahega; sidebar me `Upgrades` group ke andar jayega.
- `getting-started.md`, `configuration.md`, `introduction.md` and `index.md` already focused hain, isliye inhe unnecessary split nahi karna.

Backward-compatible docs URLs:

- Existing combined Markdown files immediately delete nahi honge.
- Old pages short overview/index pages banenge aur new canonical feature pages ko link karenge.
- Existing public URLs and useful anchors ko migration checklist me record karke preserve ya redirect karna hoga.
- Internal links, previous/next navigation, canonical URLs and search index new paths ke according update honge.

Proposed optimized docs structure:

```text
docs/
  index.md
  introduction.md
  getting-started.md
  configuration.md
  validation/
    basic.md
    form-request.md
    rule-object.md
    manual-api.md
  runtime/
    checks.md
    result.md
    blade.md
  domains/
    sync.md
    blacklist.md
    whitelist.md
    subdomains.md
    stats.md
  advanced/
    rfc-dns.md
    cache.md
    schedule.md
  maintainers/
    contributing.md
  help/
    validation.md
    sync-cache.md
    setup-blade.md
  upgrades/
    v5.md
  caching.md
  contributing.md
  deprecated-5-0-0.md
  schedule-syncing-automatically.md
  validation-and-runtime.md
  sync-and-blacklist.md
  troubleshooting.md
```

Old combined files shown at the bottom are compatibility overview pages, duplicate full documentation nahi.

Planned VitePress sidebar groups:

```ts
sidebar: [
  { text: 'Docs', items: [/* overview, introduction, installation, config */] },
  { text: 'Validation', items: [/* basic, form request, rule object, manual/API */] },
  { text: 'Runtime', items: [/* checks, detailed result, Blade */] },
  { text: 'Domain Lists', items: [/* sync, blacklist, whitelist, subdomains, stats */] },
  {
    text: 'Advanced',
    items: [
      { text: 'RFC / DNS Validation', link: '/advanced/rfc-dns.html' },
      { text: 'Caching', link: '/advanced/cache.html' },
      { text: 'Schedule Sync', link: '/advanced/schedule.html' },
    ],
  },
  { text: 'Maintainers', items: [/* contributing */] },
  { text: 'Help', items: [/* validation, sync/cache, setup/Blade */] },
  { text: 'Upgrades', items: [/* version migration pages */] },
]
```

Documentation rules:

- New feature ke saath exact Laravel usage example aur expected result add hoga.
- Har substantial feature ki apni short, focused Markdown file hogi; large mixed page me sab features combine nahi honge.
- Feature files lower-kebab-case me honge, jaise `advanced/rfc-dns.md`.
- Har new feature page ko same change me VitePress sidebar me add karna mandatory hoga.
- Existing overview/usage page new feature ka short summary aur dedicated page link rakhega.
- Har page ek primary user goal cover karega; target roughly 40-120 lines rahega, lekin clarity ke liye required examples remove nahi honge.
- Har feature page me purpose, basic usage, options, expected behavior, caveats and related links ka consistent order hoga.
- Shared setup/config snippets duplicate karne ke bajaye canonical page ko link karna hai.
- Split complete karne se pehle old headings/anchors ka inventory aur content-migration checklist banana hoga, taaki koi example ya warning miss na ho.
- RFC/DNS feature docs me plain rule, combined modes, fluent/rule-object usage (agar add ho), `intl` requirement and DNS caveat explain honge.
- New config option ke saath type, default, purpose and example add hoga.
- New/changed command ke saath exact command, options, output behavior and side effects document honge.
- Public API change ke saath method signature, return value and compatibility notes update honge.
- Internal-only refactor me public docs ko unnecessary change nahi karenge; maintainer impact ho to `contributing.md` update hoga.
- Removed/deprecated behavior ke liye migration/deprecation note add hoga; silent removal nahi hoga.
- Docs examples actual code, command and config names se match karne chahiye.

Docs split implementation order:

1. Existing pages ke headings, anchors, examples and inbound links inventory karo.
2. New feature folders/files create karke one topic at a time move and rewrite karo.
3. Moved content ko old page se remove karke short overview plus canonical links rakho.
4. `.vitepress/config.mts` sidebar ko new groups and pages ke saath update karo.
5. All internal links, related-page links and previous/next flow verify karo.
6. Search karke confirm karo ki old content ka koi section miss ya unnecessarily duplicate nahi hua.
7. VitePress production build run karke dead links and build errors fix karo.

Exit rule: every feature has one focused canonical file, every new page sidebar me visible hai, old URLs usable hain, content migration complete hai, links valid hain, and docs production build passes.

## Proposed Folder Structure

```text
src/
  Commands/
    Install.php
    Stats.php
    Sync.php
  Enums/
    LaravelVersion.php
  Facades/
    Disposable.php
  Rules/
    DisposableEmailRule.php
  Support/
    Cache.php
    Checker.php
    Domain.php
    DisposableEmailResult.php
    Email.php
    Format.php
    Matcher.php
    Modes.php
    ResponseParser.php
    SourceMap.php
    Stats.php
    UrlList.php
  LaravelDisposableEmailServiceProvider.php
```

This structure avoids new base folders unless needed. If `src/Domain` feels cleaner later, move only after tests are green.

## Safe Implementation Order

1. Add tests for current behavior.
2. Add new support classes without removing old methods.
3. Update one caller at a time.
4. Keep old methods as wrappers until all tests pass.
5. Run focused tests after every phase.
6. RFC/DNS modes ko additive feature ke roop me implement aur test karo.
7. Usi phase me related docs update karo; new feature ko docs ke bina complete mark na karo.
8. Run formatter after PHP changes: `vendor/bin/pint --dirty --format agent`.
9. Run final tests: `composer test`.
10. Run docs build from `../docs/laravel-disposable-email`: `npm run build`.

## Commands To Verify

Use minimum focused tests during each phase:

```bash
vendor/bin/pest tests/Feature/Rules
vendor/bin/pest tests/Feature/Support
vendor/bin/pest tests/Feature/Commands
vendor/bin/pest tests/Feature/ServiceProvider
```

Final verification:

```bash
vendor/bin/pint --dirty --format agent
composer test
npm --prefix ../docs/laravel-disposable-email run build
```

## Breakage Prevention Rules

- Public method names and command names will not be renamed.
- Config keys will not be renamed.
- Error messages and command output will stay same unless a test explicitly changes them.
- `Email::domains()` will not be split in the first pass because it contains the built-in source list.
- Compatibility wrappers stay until the final test suite is green.
- No dependency changes without approval.
- No package folder restructure outside `src` and `tests` unless approved; related docs updates are required.
- User-facing code and docs must ship together in the same feature/refactor work.
- New feature docs cannot be deferred to a later task.

## Expected Result

After refactor:

- Rule class small and easy to read.
- Commands mostly console I/O wrappers.
- Domain normalization and matching have one source of truth.
- Custom blacklist, whitelist, cache and remote sync logic are testable independently.
- Existing Laravel users do not need to change any usage code.
- Package docs remain aligned with actual behavior, including every new feature.

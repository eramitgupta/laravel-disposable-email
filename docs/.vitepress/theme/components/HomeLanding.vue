<script setup lang="ts">
import { withBase } from 'vitepress'
</script>

<template>
  <div class="home-redesign">
    <div class="home-section-intro">
      <span class="section-label">Email protection for Laravel</span>
      <h1>Stop disposable email addresses before they enter your Laravel app.</h1>
      <p>
        <strong>EragLaravelDisposableEmail</strong> helps you detect and block temporary email
        addresses during form validation, runtime checks, and Blade conditionals. It comes with
        <strong>110,646+</strong> known disposable domains, simple installation, remote sync
        support, custom blacklist support, and optional caching for faster lookups.
      </p>
      <div class="quick-links">
        <a :href="withBase('/getting-started')">Get started</a>
        <a :href="withBase('/validation-and-runtime')">See validation examples</a>
        <a href="https://github.com/eramitgupta/laravel-disposable-email">GitHub</a>
      </div>
    </div>

    <div class="home-hero-card">
      <div class="home-hero-copy">
        <span class="home-chip">README powered content</span>
        <h2>A clean way to keep throwaway inboxes out of your signup flow.</h2>
        <p>
          Whether you are protecting registrations, trial signups, invite systems, or internal
          tools, this package gives you a simple Laravel-friendly API for rejecting disposable email
          domains before they become a data quality problem.
        </p>

        <div class="home-mini-stats">
          <div>
            <strong>110,646+</strong>
            <span>disposable domains included</span>
          </div>
          <div>
            <strong>Laravel 10-13</strong>
            <span>supported versions</span>
          </div>
          <div>
            <strong>Rule + Facade + Blade</strong>
            <span>multiple integration points</span>
          </div>
        </div>
      </div>

      <div class="home-hero-code">
        <div class="home-code-head">
          <span></span>
          <span></span>
          <span></span>
        </div>
        <pre class="home-code-body"><span>composer require erag/laravel-disposable-email</span><span>php artisan erag:install-disposable-email</span><span></span><span>$request-&gt;validate([</span><span>    'email' =&gt; ['required', 'email', 'disposable_email'],</span><span>]);</span><span></span><span>if (Disposable::Email($email)) {</span><span>    // stop temp inboxes early</span><span>}</span></pre>
      </div>
    </div>

      <div class="home-feature-grid">
      <div class="home-feature-card">
        <span class="home-icon">RULE</span>
        <strong>Built for validation</strong>
        <p>
          Add <code>disposable_email</code> to your validation rules and block temporary inboxes at
          the form level.
        </p>
      </div>
      <div class="home-feature-card">
        <span class="home-icon">RUNTIME</span>
        <strong>Runtime checks</strong>
        <p>Check email addresses anywhere in your app using the rule class or facade.</p>
      </div>
      <div class="home-feature-card">
        <span class="home-icon">SYNC</span>
        <strong>Remote syncing</strong>
        <p>Keep your domain list fresh by syncing from a remote source whenever you need to.</p>
      </div>
      <div class="home-feature-card">
        <span class="home-icon">CACHE</span>
        <strong>Optional caching</strong>
        <p>Speed up repeated lookups in busy applications with built-in cache support.</p>
      </div>
    </div>

    <EmailCheckDemo />

    <div class="home-release-panel">
      <div class="home-release-copy">
        <span class="section-label release-label">New in this release</span>
        <h3>Shorter checks, clearer results, and better domain control.</h3>
        <p>
          Use the new <code>Disposable</code> facade for quick checks, inspect detailed match
          results when you need more context, and manage trusted or subdomain behavior from config.
        </p>
        <pre class="home-release-code"><span>Disposable::Email('test@tempmail.com');</span><span>Disposable::domain('tempmail.com');</span><span>$result = Disposable::check($email);</span></pre>
      </div>
      <div class="home-release-list">
        <div>
          <span>API</span>
          <strong>Short facade methods</strong>
          <p><code>Email</code>, <code>domain</code>, <code>check</code>, and <code>rule</code>.</p>
        </div>
        <div>
          <span>RESULT</span>
          <strong>Detailed match data</strong>
          <p>Read domain, matched domain, source, whitelist state, and array output.</p>
        </div>
        <div>
          <span>CONFIG</span>
          <strong>Whitelist and subdomains</strong>
          <p>Allow trusted domains and block disposable parent-domain subdomains.</p>
        </div>
        <div>
          <span>CLI</span>
          <strong>Stats command</strong>
          <p>Run <code>php artisan disposable:stats</code> to inspect loaded lists.</p>
        </div>
      </div>
    </div>

    <div class="home-section-intro">
      <span class="section-label">Practical usage</span>
      <h3>Everything you need to use the package with confidence.</h3>
      <p>
        The docs cover the full package workflow, from installation and validation rules to runtime
        checks, Blade directives, syncing, custom blacklists, and caching.
      </p>
    </div>

    <div class="home-content-grid">
      <div class="home-panel">
        <strong>What you get</strong>
        <ul class="home-list">
          <li>A large built-in disposable domain list ready to use out of the box.</li>
          <li>Support for both string rules and custom rule objects.</li>
          <li>Facade and runtime checks for service classes and business logic.</li>
          <li><code>@disposableEmail(...)</code> for simple conditional output in Blade views.</li>
          <li>Remote syncing, custom blacklist files, and optional caching.</li>
        </ul>
      </div>
      <div class="home-panel">
        <strong>Where it fits best</strong>
        <ol class="home-steps">
          <li>Registration forms and account creation flows.</li>
          <li>Free trial protection and abuse prevention.</li>
          <li>B2B onboarding where real business email addresses matter.</li>
          <li>Admin tools and moderation workflows.</li>
          <li>Invite, referral, and lead-quality sensitive features.</li>
        </ol>
      </div>
    </div>

    <div class="home-example-shell">
      <div class="home-example-copy">
        <span class="section-label">Code examples</span>
        <h3>Use it the way real Laravel apps are built.</h3>
        <p>
          Start with validation at the request layer, then add runtime checks anywhere your business
          logic needs a simple yes or no answer.
        </p>
      </div>

      <div class="home-example-grid">
        <div class="home-example-card">
          <strong>Form Request</strong>
          <pre class="home-example-code"><span>public function rules(): array</span><span>{</span><span>    return [</span><span>        'email' =&gt; ['required', 'email', 'disposable_email'],</span><span>    ];</span><span>}</span></pre>
        </div>
        <div class="home-example-card">
          <strong>Facade</strong>
          <pre class="home-example-code"><span>use Disposable;</span><span></span><span>if (Disposable::Email($email)) {</span><span>    throw ValidationException::withMessages([</span><span>        'email' =&gt; 'Use a permanent inbox.'</span><span>    ]);</span><span>}</span></pre>
        </div>
        <div class="home-example-card">
          <strong>Blade</strong>
          <pre class="home-example-code"><span>@disposableEmail($email)</span><span>  &lt;p class="text-red-600"&gt;Disposable email detected&lt;/p&gt;</span><span>@else</span><span>  &lt;p class="text-green-600"&gt;Email looks good&lt;/p&gt;</span><span>@enddisposableEmail</span></pre>
        </div>
      </div>
    </div>

    <div class="home-cta-panel">
      <span class="section-label">Explore docs</span>
      <h3>Install it, configure it, and keep your email checks up to date.</h3>
      <p>
        Start with <code>Getting Started</code>, then move through validation, configuration,
        syncing, and caching to set up a complete disposable email blocking workflow.
      </p>
      <div class="quick-links">
        <a :href="withBase('/getting-started')">Installation guide</a>
        <a :href="withBase('/configuration')">Configuration options</a>
        <a :href="withBase('/sync-and-blacklist')">Sync and blacklist</a>
        <a :href="withBase('/caching')">Caching notes</a>
      </div>
    </div>
  </div>
</template>

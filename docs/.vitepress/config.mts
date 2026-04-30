import { defineConfig } from 'vitepress'

export default defineConfig({
  base: '/laravel-disposable-email/',
  title: 'Laravel Disposable Email',
  description: 'Disposable email detection for Laravel applications.',
  cleanUrls: true,
  lastUpdated: true,
  sitemap: {
    hostname: 'https://erag.github.io/laravel-disposable-email'
  },
  head: [
    ['meta', { name: 'theme-color', content: '#f53003' }],
    ['meta', { name: 'author', content: 'Er Amit Gupta' }],
    ['meta', { name: 'robots', content: 'index, follow' }],
    [
      'meta',
      {
        name: 'keywords',
        content: 'laravel disposable email, temporary email validation, disposable email blocker, laravel email validation, temp mail protection, fake email detection'
      }
    ],
    ['meta', { property: 'og:site_name', content: 'Laravel Disposable Email' }],
    ['meta', { property: 'og:title', content: 'Laravel Disposable Email' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:url', content: 'https://erag.github.io/laravel-disposable-email/' }],
    ['meta', { property: 'og:image', content: 'https://avatars.githubusercontent.com/u/72160684?v=4&size=64' }],
    ['meta', { name: 'twitter:card', content: 'summary_large_image' }],
    ['meta', { name: 'twitter:title', content: 'Laravel Disposable Email' }],
    [
      'meta',
      {
        name: 'twitter:description',
        content: 'Block disposable email addresses with validation rules, facades, Blade directives, and remote sync support.'
      }
    ],
    ['meta', { name: 'twitter:image', content: 'https://avatars.githubusercontent.com/u/72160684?v=4&size=64' }],
    [
      'meta',
      {
        property: 'og:description',
        content: 'Block disposable email addresses with validation rules, facades, Blade directives, and remote sync support.'
      }
    ],
    [
      'link',
      {
        rel: 'icon',
        href: 'https://avatars.githubusercontent.com/u/72160684?v=4&size=64'
      }
    ],
    [
      'link',
      {
        rel: 'canonical',
        href: 'https://erag.github.io/laravel-disposable-email/'
      }
    ]
  ],
  themeConfig: {
    nav: [
      { text: 'Get started', link: '/introduction' },
    ],
    sidebar: [
      {
        text: 'Docs',
        items: [
          { text: 'Overview', link: '/' },
          { text: 'Introduction', link: '/introduction' },
          { text: 'Installation', link: '/getting-started' },
          { text: 'Config', link: '/configuration' }
        ]
      },
      {
        text: 'Usage',
        items: [
          { text: 'Validation and Runtime', link: '/validation-and-runtime' },
          { text: 'Sync and Blacklist', link: '/sync-and-blacklist' },
          { text: 'Schedule Sync', link: '/schedule-syncing-automatically' },
          { text: 'Caching', link: '/caching' },
          { text: 'Troubleshooting', link: '/troubleshooting' },
          { text: 'Contributing', link: '/contributing' },
          { text: 'Deprecated 5.0.0', link: '/deprecated-5-0-0' }
        ]
      }
    ],
    search: {
      provider: 'local'
    },
    outline: {
      level: [2, 3],
      label: 'On this page'
    },
    docFooter: {
      prev: 'Previous',
      next: 'Next'
    },
    footer: {
        message: 'MIT License. Copyright Er Amit Gupta',
    },
    socialLinks: [
      { icon: 'github', link: 'https://github.com/eramitgupta/laravel-disposable-email' }
    ]
  }
})

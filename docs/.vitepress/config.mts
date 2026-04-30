import { defineConfig } from 'vitepress'

const siteUrl = 'https://eramitgupta.github.io/laravel-disposable-email'
const siteOrigin = 'https://eramitgupta.github.io'
const siteBase = '/laravel-disposable-email'

const canonicalUrl = (page: string): string => {
  const path = page
    .replace(/(^|\/)index\.md$/, '$1')
    .replace(/\.md$/, '')

  return `${siteUrl}${path ? `/${path}` : '/'}`
}

const searchConsoleVerification = process.env.GOOGLE_SITE_VERIFICATION

export default defineConfig({
  base: '/laravel-disposable-email/',
  title: 'Laravel Disposable Email',
  description: 'Disposable email detection for Laravel applications.',
  cleanUrls: true,
  lastUpdated: true,
  sitemap: {
    hostname: siteOrigin,
    transformItems: (items) => items.map((item) => {
      const path = item.url.startsWith('/') ? item.url : `/${item.url}`
      const url = path === '/' || !path.startsWith(siteBase)
        ? `${siteBase}${path}`
        : path

      return {
        ...item,
        url
      }
    })
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
    ]
  ].concat(searchConsoleVerification ? [
    ['meta', { name: 'google-site-verification', content: searchConsoleVerification }]
  ] : []),
  transformHead({ page }) {
    const url = canonicalUrl(page)

    return [
      ['link', { rel: 'canonical', href: url }],
      ['meta', { property: 'og:url', content: url }],
      [
        'script',
        { type: 'application/ld+json' },
        JSON.stringify({
          '@context': 'https://schema.org',
          '@type': 'SoftwareSourceCode',
          name: 'Laravel Disposable Email',
          description: 'Disposable email detection for Laravel applications.',
          url,
          codeRepository: 'https://github.com/eramitgupta/laravel-disposable-email',
          programmingLanguage: 'PHP',
          license: 'https://opensource.org/licenses/MIT',
          author: {
            '@type': 'Person',
            name: 'Er Amit Gupta'
          }
        })
      ]
    ]
  },
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

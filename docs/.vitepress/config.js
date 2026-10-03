import baseConfig from '@cakephp/docs-skeleton/config'
import { createRequire } from 'module'

const require = createRequire(import.meta.url)
const tocEn = require('./toc_en.json')
const tocEs = require('./toc_es.json')
const tocFr = require('./toc_fr.json')
const tocJa = require('./toc_ja.json')

const versions = {
  text: '4.x',
  items: [
    { text: '4.x (current)', link: 'https://book.cakephp.org/authorization/4/', target: '_self' },
    { text: '3.x', link: 'https://book.cakephp.org/authorization/3/', target: '_self' },
    { text: '2.x', link: 'https://book.cakephp.org/authorization/2/en/', target: '_self' },
  ],
}

export default {
  extends: baseConfig,
  srcDir: '.',
  title: 'Authorization',
  description: 'CakePHP Authorization Documentation',
  base: '/authorization/4/',
  rewrites: {
    'en/:slug*': ':slug*',
  },
  sitemap: {
    hostname: 'https://book.cakephp.org/authorization/4/',
  },
  themeConfig: {
    siteTitle: false,
    pluginName: "Authorization",
    socialLinks: [
      { icon: 'github', link: 'https://github.com/cakephp/authorization' },
    ],
    editLink: {
      pattern: 'https://github.com/cakephp/authorization/edit/4.x/docs/:path',
      text: 'Edit this page on GitHub',
    },
    sidebar: tocEn,
    nav: [
      { text: 'CakePHP', link: 'https://cakephp.org' },
      { text: 'API', link: 'https://api.cakephp.org/authorization/' },
      { ...versions },
    ],
  },
  locales: {
    root: {
      label: 'English',
      lang: 'en',
      themeConfig: {
        sidebar: tocEn,
      },
    },
    es: {
      label: 'Español',
      lang: 'es',
      themeConfig: {
        sidebar: tocEs,
      },
    },
    fr: {
      label: 'Français',
      lang: 'fr',
      themeConfig: {
        sidebar: tocFr,
      },
    },
    ja: {
      label: '日本語',
      lang: 'ja',
      themeConfig: {
        sidebar: tocJa,
      },
    },
  },
}

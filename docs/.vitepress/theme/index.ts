import type { Theme } from 'vitepress'
import DefaultTheme from 'vitepress/theme'
import EmailCheckDemo from './components/EmailCheckDemo.vue'
import HomeLanding from './components/HomeLanding.vue'
import './style.css'

export default {
  extends: DefaultTheme,
  enhanceApp({ app }) {
    app.component('EmailCheckDemo', EmailCheckDemo)
    app.component('HomeLanding', HomeLanding)
  }
} satisfies Theme

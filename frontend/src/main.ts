import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura'

// Import PrimeVue components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Card from 'primevue/card'
import Menu from 'primevue/menu'
import Menubar from 'primevue/menubar'
import Toast from 'primevue/toast'
import ToastService from 'primevue/toastservice'
import Tooltip from 'primevue/tooltip'
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import Textarea from 'primevue/textarea'
import Paginator from 'primevue/paginator'
import Chart from 'primevue/chart'

// Import Chart.js
import 'chart.js/auto'

// Import PrimeIcons
import 'primeicons/primeicons.css'

// Import Tailwind CSS
import './assets/main.css'

// Import Responsive CSS
import './assets/responsive.css'

// Import Modal Styles
import './assets/modal-styles.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(PrimeVue, {
  theme: {
    preset: Aura,
    options: {
      prefix: 'p',
      darkModeSelector: '.dark-mode',
      cssLayer: false
    }
  }
})
app.use(ToastService)

// Register PrimeVue directives
app.directive('tooltip', Tooltip)

// Register PrimeVue components globally
app.component('Button', Button)
app.component('InputText', InputText)
app.component('DataTable', DataTable)
app.component('Column', Column)
app.component('Card', Card)
app.component('Menu', Menu)
app.component('Menubar', Menubar)
app.component('Toast', Toast)
app.component('Dialog', Dialog)
app.component('Select', Select)
app.component('DatePicker', DatePicker)
app.component('Textarea', Textarea)
app.component('Paginator', Paginator)
app.component('Chart', Chart)

app.mount('#app')

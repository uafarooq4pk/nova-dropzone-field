import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
    app.component('index-dropzone-field', IndexField)
    app.component('detail-dropzone-field', DetailField)
    app.component('form-dropzone-field', FormField)
})

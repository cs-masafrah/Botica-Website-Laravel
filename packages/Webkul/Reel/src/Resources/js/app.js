import { createApp } from 'vue';
import VCreateReelForm from './components/VCreateReelForm.vue'; // If using .vue files

const app = createApp({});

app.component('v-create-reel-form', {
    template: '#v-create-reel-form-template',  // using inline template from Blade script
    data() {
        return {
            isLoading: false,
        };
    },
    methods: {
        openModal() {
            this.$refs.modal.open();
        },
        create(params, { resetForm, setErrors }) {
            this.isLoading = true;

            this.$axios.post('/admin/reel/store', params)
                .then(response => {
                    this.$refs.modal.close();
                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                    resetForm();
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        setErrors(error.response.data.errors);
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        }
    }
});

app.mount('#app');

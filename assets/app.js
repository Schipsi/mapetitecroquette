import Alpine from 'alpinejs'

window.Alpine = Alpine

Alpine.start()

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';

import axios from 'axios';



axios.defaults.baseURL = `/api`;
axios.defaults.withCredentials = true; // ✅ obligatorio para enviar cookies
axios.defaults.xsrfCookieName = 'XSRF-TOKEN'; // cookie que Laravel genera
axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN'; // encabezado que Laravel espera
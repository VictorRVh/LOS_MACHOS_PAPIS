<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const codigo = route.params.codigo

const cargando = ref(true)
const valido = ref(false)
const mensaje = ref('')

onMounted(async () => {
  try {
    const response = await fetch(`/verificar-certificado/${codigo}`)

    if (!response.ok) {
      throw new Error('No válido')
    }

    const data = await response.json()

    valido.value = data.estado
    mensaje.value = data.mensaje
  } catch (error) {
    valido.value = false
    mensaje.value = 'Certificado NO válido'
  } finally {
    cargando.value = false
  }
})
</script>


<template>
  <div class="contenedor">

    <!-- Cargando -->
    <div v-if="cargando" class="card loading">
      <div class="spinner"></div>
      <p>Verificando autenticidad del certificado...</p>
    </div>

    <!-- Resultado -->
    <div v-else class="card resultado" :class="valido ? 'valido' : 'invalido'">

      <div class="icono">
        <span v-if="valido">✔</span>
        <span v-else>✖</span>
      </div>

      <h1>
        {{ valido ? 'Certificado Verificado' : 'Certificado No Válido' }}
      </h1>

      <p class="mensaje">{{ mensaje }}</p>

      <div class="detalle">
        <span>Código</span>
        <strong>{{ codigo }}</strong>
      </div>

      <button class="btn" @click="router.push('/')">
        Volver al inicio
      </button>

    </div>

  </div>
</template>

<style scoped>
.contenedor {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a, #1e293b);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

/* Card base */
.card {
  background: #ffffff;
  border-radius: 18px;
  padding: 40px 35px;
  width: 100%;
  max-width: 420px;
  text-align: center;
  box-shadow: 0 25px 50px rgba(0,0,0,.25);
  animation: aparecer .6s ease;
}

/* Animación */
@keyframes aparecer {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Cargando */
.loading p {
  margin-top: 20px;
  font-size: 16px;
  color: #475569;
}
.spinner {
  width: 55px;
  height: 55px;
  border: 5px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: auto;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Resultado */
.resultado.valido {
  border-top: 8px solid #22c55e;
}
.resultado.invalido {
  border-top: 8px solid #ef4444;
}

/* Icono */
.icono {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  margin: auto;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 46px;
  margin-bottom: 20px;
}
.valido .icono {
  background: #dcfce7;
  color: #16a34a;
}
.invalido .icono {
  background: #fee2e2;
  color: #dc2626;
}

/* Textos */
h1 {
  font-size: 24px;
  margin-bottom: 10px;
}
.mensaje {
  font-size: 15px;
  color: #475569;
  margin-bottom: 25px;
}

/* Detalle */
.detalle {
  background: #f1f5f9;
  border-radius: 10px;
  padding: 12px;
  margin-bottom: 25px;
  font-size: 14px;
}
.detalle span {
  display: block;
  color: #64748b;
}
.detalle strong {
  font-size: 16px;
}

/* Botón */
.btn {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  color: white;
  border: none;
  padding: 12px 25px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 15px;
  transition: transform .2s, box-shadow .2s;
}
.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(37,99,235,.4);
}
</style>

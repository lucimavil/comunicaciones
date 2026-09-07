@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="text-muted small">Panel general</span>
            <h4 class="fw-bold mb-0">
                Dashboard de Mensajería por WhatsApp
            </h4>
        </div>

        <a href="{{ route('mensajeria.calendario') }}"
           class="btn btn-outline-primary">

            <i class="bi bi-calendar3 me-1"></i>
            Calendario de envíos
        </a>
    </div>

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f9fafb;
      margin: 0;
      padding: 20px;
      color: #333;
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
    }
    .dashboard {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
      gap: 20px;
    }
    .card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      padding: 20px;
    }
    canvas {
      width: 100% !important;
      height: 300px !important;
    }
    .kpis {
      display: flex;
      justify-content: space-around;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 10px;
    }
    .kpi {
      background: #fff;
      padding: 15px 25px;
      border-radius: 12px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      text-align: center;
      flex: 1;
      min-width: 150px;
    }
    .kpi span {
      display: block;
      font-size: 1.8em;
      font-weight: bold;
      color: #007bff;
    }
    #graficoEstados {
      max-width: 600px;
      margin: 0 auto;
    }

  </style>


<div id="filtros-fechas" class="container mb-4">
  <div class="row g-3 align-items-end">
    <div class="col-md-4">
      <label for="fechaInicio" class="form-label">Desde</label>
      <input type="date" id="fechaInicio" class="form-control">
    </div>
    <div class="col-md-4">
      <label for="fechaFin" class="form-label">Hasta</label>
      <input type="date" id="fechaFin" class="form-control">
    </div>
    <div class="col-md-4 d-grid">
      <button id="btnFiltrar" class="btn btn-primary btn-lg">
        <i class="bi bi-filter"></i> Filtrar
      </button>
    </div>
  </div>
</div>


<div id="kpi-container" class="kpi-grid"></div>


  <div class="dashboard">
    <div class="card" >
      <h3 class="card-title text-center">Mensajes por Estado</h3>
      <canvas id="graficoEstados"></canvas>
    </div>

    <div class="card">
      <h3 class="card-title text-center">Tasa de Lectura (%)</h3>
      <canvas id="tasaLectura"></canvas>
    </div>

   
    <div class="card">
    <h3 class="card-title text-center">Confirmaciones y Cancelaciones</h3>
      <canvas id="graficoCancelaciones"></canvas>
  
  </div>
</div>
<div id="kpi-container" class="my-4"></div>
<div id="tabla-final-container" class="my-4"></div>
  </div>

  <script>
   // === Función para obtener fecha actual en formato YYYY-MM-DD ===
function obtenerFechaActual() {
  const hoy = new Date();
  const yyyy = hoy.getFullYear();
  const mm = String(hoy.getMonth() + 1).padStart(2, '0');
  const dd = String(hoy.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
}

// === Función principal para cargar datos ===
async function cargarDatos(startDate, endDate) {
  try {
    // Si no se pasaron fechas, usar la actual
    const start = startDate || obtenerFechaActual();
    const end = endDate || obtenerFechaActual();

    // Construir URLs
  const API_MENSAJES =
      `{{ route('mensajeria.api.mensajes') }}?start=${start}&end=${end}`;

  const API_ENVIOS =
      `{{ route('mensajeria.api.envios') }}?start=${start}&end=${end}`;
      
    // Llamadas en paralelo
    const [respMensajes, respEnvios] = await Promise.all([
      fetch(API_MENSAJES),
      fetch(API_ENVIOS)
    ]);

    const [datosMensajes, datosEnvios] = await Promise.all([
      respMensajes.json(),
      respEnvios.json()
    ]);
    
    // === MAPA ID_ENVIO → TEMPLATE desde datosEnvios ===
    const mapaTemplates = {};

    datosEnvios.forEach(e => {
        if (e.ID && e.TEMPLATE) {
            mapaTemplates[e.ID] = e.TEMPLATE;
        }
    });
    // Mostrar datos
    renderGraficos(datosMensajes, datosEnvios);

  } catch (error) {
    console.error("Error al cargar datos:", error);
  }
}




// === Renderizar los gráficos y KPIs ===
function renderGraficos(datos, datosEnvios) {
  // Agrupar por ID_ENVIO
  const envios = {};
  for (const msg of datos) {
    const key = msg.ID_ENVIO ?? msg.id_envio ?? msg.id ?? null;
    if (!key) continue;
    if (!envios[key]) envios[key] = [];
    envios[key].push(msg);
  }

  // === Crear mapa ID_ENVIO -> TEMPLATE (desde la API de envíos) ===
  const mapaTemplates = {};
  for (const e of datosEnvios) {
    const idKey = e.ID ?? e.ID_ENVIO ?? e.id ?? null;
    if (!idKey) continue;

    // Guardar template
    mapaTemplates[idKey] = e.TEMPLATE ?? "Sin template";
  }
// KPIs principales

// === Recalcular estado según fechas ===
const datosNormalizados = datos.map(m => {
  let estadoCalculado = m.ESTADO;

  // Solo revisar los estados 4, 5 o 6
  if ([4, 5, 6].includes(m.ESTADO)) {
    const send = m.FECHA_SEND;
    const recibido = m.FECHA_RECIBIDO;
    const leido = m.FECHA_LEIDO;

    if (!send && !recibido && !leido) {
      estadoCalculado = 1; // Aceptado por Meta
    } else if (send && recibido && !leido) {
      estadoCalculado = 3; // Recibido
    } else if (send && recibido && leido) {
      estadoCalculado = 4; // Leído
    }
  }

  return { ...m, ESTADO: estadoCalculado };
});


const totalMensajes = datosNormalizados.filter(m => m.ESTADO !== 8 && m.ESTADO !== 7).length;
const totalMensajesDeCancelacion = datosNormalizados.filter(m => m.ESTADO === 7).length;
const leidos = datosNormalizados.filter(m => m.ESTADO === 4).length;
const recibidos = datosNormalizados.filter(m => m.ESTADO === 3).length;
const confirmados = datosNormalizados.filter(m => m.ESTADO === 5).length;
const tasaLectura = totalMensajes ? ((leidos / totalMensajes) * 100).toFixed(1) : 0;


  const calcularSegundos = (a, b) => (new Date(b) - new Date(a)) / 1000;
  const tiemposEnvioLectura = datos
    .filter(m => m.FECHA_LEIDO && m.FECHA_ENVIO)
    .map(m => calcularSegundos(m.FECHA_ENVIO, m.FECHA_LEIDO));

  const promedioLectura = tiemposEnvioLectura.length
    ? (tiemposEnvioLectura.reduce((a,b) => a+b) / tiemposEnvioLectura.length).toFixed(1)
    : 0;
// === KPIs por estado ===
const estadosDef = [
  { id: 1, label: "Aceptado por Meta (listo para enviar)", color: "primary", icon: "bi-check-circle" },
  { id: 2, label: "Enviado (aún no recibido por el paciente)", color: "info", icon: "bi-send" },
  { id: 3, label: "Recibido por el usuario (doble tilde gris)", color: "success", icon: "bi-inbox" },
  { id: 4, label: "Leído por el usuario (doble tilde azul)", color: "warning", icon: "bi-eye" },
  { id: 5, label: "Confirmado (agenda registrada)", color: "success", icon: "bi-calendar-check" },
  { id: 6, label: "Cancelado por el paciente", color: "danger", icon: "bi-x-circle" },
  { id: 7, label: "Cancelado por el sistema", color: "danger", icon: "bi-exclamation-octagon" },
  { id: 8, label: "Revisar (requiere control)", color: "secondary", icon: "bi-search" },
  { id: 9, label: "Fallo antes de enviar", color: "dark", icon: "bi-bug" },
  { id: 10, label: "Eliminado", color: "secondary", icon: "bi-trash" },
  { id: 11, label: "No aceptado por Meta", color: "dark", icon: "bi-exclamation-triangle" }
];


// Contar mensajes por estado
const conteos = {};
datos.forEach(m => {
  conteos[m.ESTADO] = (conteos[m.ESTADO] || 0) + 1;
});


// Generar tarjetas de KPI dinámicamente
let html = `
  <div class="container my-4">
    <div class="row text-center g-3">
`;

// Tarjeta de total general
html += `
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-sm border-0 bg-light h-100">
      <div class="card-body">
        <i class="bi bi-chat-dots text-primary" style="font-size: 1.5rem;"></i>
        <h2 class="card-title text-primary my-2">${totalMensajes}</h2>
        <p class="card-text small text-muted">Total de mensajes</p>
      </div>
    </div>
  </div>
`;
// Tarjeta de total general
html += `
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-sm border-0 bg-light h-100">
      <div class="card-body">
        <i class="bi bi-chat-dots text-primary" style="font-size: 1.5rem;"></i>
        <h2 class="card-title text-primary my-2">${totalMensajesDeCancelacion}</h2>
        <p class="card-text small text-muted">Total de mensajes cancelaciones automaticas</p>
      </div>
    </div>
  </div>
`;


estadosDef.forEach(est => {
  const cantidad = conteos[est.id] || 0;
  html += `
    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm border-0 bg-light h-100">
        <div class="card-body">
          <i class="bi ${est.icon} text-${est.color}" style="font-size: 1.5rem;"></i>
          <h2 class="card-title text-${est.color} my-2">${cantidad}</h2>
          <p class="card-text small text-muted">${est.label}</p>
        </div>
      </div>
    </div>
  `;
});

html += `
    </div>
  </div>
`;

document.getElementById("kpi-container").innerHTML = html;

 // Definir colores por estado (coinciden con los KPI)
 const coloresPorEstado = {
  1: '#4CAF50',  // Aceptado Meta
  2: '#2196F3',  // Enviado
  3: '#0dcaf0',  // Recibido
  4: '#ffc107',  // Leído
  5: '#28a745',  // Confirmado
  6: '#dc3545',  // Cancelado Usuario
  7: '#d63384',  // Cancelado Sistema
  8: '#6c757d',  // Revisar
  9: '#212529',  // Fallo
  10: '#adb5bd', // Deleted
  11: '#fd7e14'  // No Aceptado Meta
};

// Preparar datos para el gráfico
const labelsEstados = estadosDef.map(e => e.label);
const dataEstados = estadosDef.map(e => conteos[e.id] || 0);
const colores = estadosDef.map(e => coloresPorEstado[e.id] || '#795548');



// Crear o actualizar el gráfico
const ctxEstados = document.getElementById('graficoEstados');

// Si ya existe un gráfico previo, destruirlo antes de crear uno nuevo
if (window.graficoEstadosChart) {
  window.graficoEstadosChart.destroy();
}

window.graficoEstadosChart = new Chart(ctxEstados, {
  type: 'doughnut',
  data: {
    labels: labelsEstados,
    datasets: [{
      data: dataEstados,
      backgroundColor: colores,
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          boxWidth: 12,
          font: { size: 11 }
        }
      },
      title: {
        display: true,
        text: 'Distribución de Mensajes por Estado',
        font: { size: 16, weight: 'bold' }
      }
    }
  }
});

// ---------------------
// Gráfico 2 - Tasa de lectura por TEMPLATE
// ---------------------

// ids y tasas (asegurando números)
const ids = Object.keys(envios);

let tasas = ids.map(id => {
  const arr = envios[id];

  // Obtener template
  const template = mapaTemplates[id] || "Sin template";
  const templateLower = template.toLowerCase();

  // === EXCLUIR cancelaciones automáticas ===
  const esCancelacion = 
      templateLower.includes("cancelacion_automatica") ||
      arr.some(m => m.TIPO_MENSAJE === 4) ||
      arr.some(m => m.TIPO_MENSAJE === 6);

    if (esCancelacion) return null;

 // Filtramos mensajes válidos (sin cancelaciones ni TIPO 5 si querés seguir sacando ese también)
 const filtrados = arr.filter(m => m.TIPO_MENSAJE !== 4 && m.TIPO_MENSAJE !== 6);

  const total = filtrados.length || 1;
 // === ESTADOS que cuentan como leídos ===
    const leidos = filtrados.filter(m =>
      m.ESTADO === 4 ||  // leído
      m.ESTADO === 5 ||  // confirmado
      m.ESTADO === 6     // cancelado por usuario
    ).length;


  const tasaNum = (leidos / total) * 100;

  return {
      template,
      tasa: Number(tasaNum.toFixed(1))
    };
  })
  .filter(x => x !== null);  // eliminar excluidos

// ordenar por tasa descendente
tasas.sort((a, b) => b.tasa - a.tasa);

// labels = TEMPLATE
const labelsOrdenados = tasas.map(t => t.template);
const valoresTasas = tasas.map(t => t.tasa);

// destruir gráfico anterior
if (window.tasaLecturaChart) {
  window.tasaLecturaChart.destroy();
}

const ctxTasa = document.getElementById('tasaLectura').getContext('2d');

window.tasaLecturaChart = new Chart(ctxTasa, {
  type: 'bar',
  data: {
    labels: labelsOrdenados,   // ← ← ← AHORA SE VE EL TEMPLATE
    datasets: [{
      label: '% Leídos',
      data: valoresTasas,
      backgroundColor: 'rgba(0, 123, 255, 0.85)',
      borderColor: 'rgba(0, 123, 255, 1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => `${ctx.formattedValue}%`
        }
      },
      title: {
        display: true,
        text: 'Tasa de lectura por TEMPLATE'
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        max: 100,
        ticks: { callback: v => v + '%' }
      }
    }
  }
});


// ---------------------
// Gráfico 3 - Promedio de tiempo entre envío y lectura (corregido)
// Mostrarlo como barra horizontal (más claro para un único valor)
// ---------------------
// === GRÁFICO: Confirmaciones y Cancelaciones por Tipo de Mensaje ===

// Definir los tipos de mensaje
const tiposMensaje = {
  1: "Consultas (72hs)",
  2: "Exámenes (72hs)",
  3: "Lista de Espera"
};

// Crear mapa rápido de ID_ENVIO → tipo_mensaje
const mapaTipos = {};
datosEnvios.forEach(e => {
  if (e.ID && e.TIPO_MENSAJE) {
    mapaTipos[e.ID] = e.TIPO_MENSAJE;
  }
});

// Inicializar resumen por tipo
const resumenTipos = {};
Object.keys(tiposMensaje).forEach(id => {
  resumenTipos[id] = { total: 0, confirmados: 0, canceladosUsuario: 0, canceladosSistema: 0 };
});

// Recorrer los mensajes y agrupar por tipo
datos.forEach(m => {
  const tipo = mapaTipos[m.ID_ENVIO];
  if (!tipo || !tiposMensaje[tipo]) return;

  resumenTipos[tipo].total++;

  if (m.ESTADO === 5) resumenTipos[tipo].confirmados++;
  if (m.ESTADO === 6) resumenTipos[tipo].canceladosUsuario++;
  if (m.ESTADO === 7) resumenTipos[tipo].canceladosSistema++;
});

// Preparar datos para el gráfico
const labelsTipos = Object.values(tiposMensaje);

const datosConfirmados = Object.keys(tiposMensaje).map(
  id => resumenTipos[id].total
    ? (resumenTipos[id].confirmados / resumenTipos[id].total * 100).toFixed(1)
    : 0
);

const datosCanceladosUsuario = Object.keys(tiposMensaje).map(
  id => resumenTipos[id].total
    ? (resumenTipos[id].canceladosUsuario / resumenTipos[id].total * 100).toFixed(1)
    : 0
);

const datosCanceladosSistema = Object.keys(tiposMensaje).map(
  id => resumenTipos[id].total
    ? (resumenTipos[id].canceladosSistema / resumenTipos[id].total * 100).toFixed(1)
    : 0
);

// Crear o actualizar el gráfico
const ctxCancelaciones = document.getElementById('graficoCancelaciones');
if (window.graficoCancelacionesChart) {
  window.graficoCancelacionesChart.destroy();
}

window.graficoCancelacionesChart = new Chart(ctxCancelaciones, {
  type: 'bar',
  data: {
    labels: labelsTipos,
    datasets: [
      {
        label: '% Confirmados',
        data: datosConfirmados,
        backgroundColor: '#28a745' // verde
      },
      {
        label: '% Cancelados por Usuario',
        data: datosCanceladosUsuario,
        backgroundColor: '#dc3545' // rojo
      },
      {
        label: '% Cancelados por Sistema',
        data: datosCanceladosSistema,
        backgroundColor: '#6c757d' // gris
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      x: { stacked: false },
      y: {
        beginAtZero: true,
        max: 100,
        title: { display: true, text: 'Porcentaje (%)' }
      }
    },
    plugins: {
      title: {
        display: true,
        text: 'Confirmaciones y Cancelaciones por Tipo de Mensaje',
        font: { size: 16, weight: 'bold' }
      },
      legend: { position: 'bottom' }
    }
  }
});

}

// === Manejar el evento del botón de filtro ===
document.getElementById('btnFiltrar').addEventListener('click', () => {
  const inicio = document.getElementById('fechaInicio').value;
  const fin = document.getElementById('fechaFin').value;

  if (!inicio || !fin) {
    alert("Por favor, seleccione ambas fechas.");
    return;
  }

  cargarDatos(inicio, fin);
});

// === Cargar datos por defecto (día actual) ===
window.addEventListener('load', () => {
  const hoy = obtenerFechaActual();
  document.getElementById('fechaInicio').value = hoy;
  document.getElementById('fechaFin').value = hoy;
  cargarDatos();
});  
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>


</div>
@endsection
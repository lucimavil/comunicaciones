@extends('layouts.app')

@section('content')
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- FullCalendar + Bootstrap 5 Theme -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/bootstrap5/index.global.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/bootstrap5/index.global.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


<style>
  .fc-event-title.fc-sticky {
    font-family: var(--bs-body-font-family) !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    color: var(--bs-body-color) !important;
}
.fc-event {
    cursor: pointer !important;
}
.fc-event-extra {
  font-size: 0.75rem;
  margin-top: 2px;
  line-height: 1.1;
  color: #444;
}

  </style>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="text-muted small">Panel general</span>
            <h4 class="fw-bold mb-0">
                Calendario de envíos de mensajería por WhatsApp
            </h4>
        </div>

       
    </div>

<div style="max-width: 1400px; margin: auto;">
    <div id="calendar"></div>
</div>
<div id="detalle-evento" class="card mt-3" style="display:none;">
    <div class="card-body">
        <h4 class="card-title">Detalle del envío</h4>
        <div id="contenido-detalle"></div>
    </div>
</div>

<!-- 📌 Filtros y tabla de turnos -->
<div id="detalle-turnos" class="card mt-4" style="display:none;">
    <div class="card-body">
        <h4 class="card-title">Detalle de turnos</h4>
        <button id="btnExcel" class="btn btn-success mb-2">📥 Descargar Excel</button>

        <!-- Filtros -->
        <div class="row g-2 mb-3">
          <div class="col-md-3">
            <select id="filtroEstado" class="form-control">
                <option value="">Filtrar por estado</option>
            </select>
          </div>
          <div class="col-md-3">
    <select id="filtroEstadoMensaje" class="form-control">
        <option value="">Estado mensaje…</option>
        <option value="1">Aceptado Meta</option>
        <option value="2">Enviado</option>
        <option value="3">Recibido</option>
        <option value="4">Leído</option>
        <option value="5">Confirmado (Agenda)</option>
        <option value="6">Cancelado usuario</option>
        <option value="7">Cancelado sistema</option>
        <option value="8">Revisar</option>
        <option value="9">Falló envío</option>
        <option value="10">Eliminado</option>
        <option value="11">No aceptado Meta</option>
    </select>
</div>

            <div class="col-md-3">
                <input id="filtroNombre" type="text" class="form-control" placeholder="Filtrar por nombre">
            </div>
            <div class="col-md-2">
                <input id="filtroDni" type="text" class="form-control" placeholder="Filtrar por DNI">
            </div>
            <div class="col-md-2">
                <input id="filtroTel" type="text" class="form-control" placeholder="Filtrar por teléfono">
            </div>
            <div class="col-md-2">
                <input id="filtroFecha" type="date" class="form-control">
            </div>
            <div class="col-md-3">
                <input id="filtroAgenda" type="text" class="form-control" placeholder="Filtrar por agenda">
            </div>
        </div>

        <div id="contenido-turnos"></div>
    </div>
</div>


<script>
// =====================
// CONFIG
// =====================
const API_BASE_URL = "{{ route('mensajeria.api.envios') }}";

// === UTILIDAD: Formato YYYY-MM-DD ===
function formatDate(date) {
  return date.toISOString().split("T")[0];
}

// =====================
// CARGAR EVENTOS DESDE LA API
// =====================
async function fetchEnvios(start, end) {
  const url = `${API_BASE_URL}?start=${start}&end=${end}`;

  const resp = await fetch(url);
  const data = await resp.json();

  return data.map(e => {
  
  // === DEFINIR COLOR SEGÚN TEMPLATE ===
  let color = "";
  if (e.TEMPLATE === "cancelacion_automatica" ) {
    color = "#F08C00"; 
  } else if (e.TEMPLATE === "recordatorio_lista_espera") {
    color = "#F5BD01"; // azul por defecto del calendario
  } else if (e.TEMPLATE === "recordatorio_turno") {
    color = "#3BA72F"; //
  }

  // === DEFINIR TEXTO EXTRA SEGÚN TIPO_MENSAJE ===
let extra = "";

if (e.TEMPLATE === "cancelacion_automatica") {
  if (e.TIPO_MENSAJE === 5) {
    extra = " (Exámenes)";
  } else if (e.TIPO_MENSAJE === 6) {
    extra = " (Lista de espera)";
  } else {
    extra = " (Consultorio)";
  }
}

  return {
    id: e.ID,
    title: `${e.ID} – ${e.TEMPLATE.replace(/_/g, " ")}`,
    extra: extra,   // <<< guardamos el texto extra
    start: e.FECHA_ENVIO,
    allDay: true,
    backgroundColor: color,
    borderColor: color,
    extendedProps: {
      tipo: e.TIPO_MENSAJE,
      template: e.TEMPLATE,
      enviados: e.CANTIDAD_MENSAJES_ENVIADOS,
      total: e.CANTIDAD_MENSAJES_TOTAL,
      sinPaciente: e.CANTIDAD_MENSAJES_SIN_PACIENTE,
      sinTel: e.CANTIDAD_MENSAJES_SIN_TELEFONO,
      sinFormato: e.CANTIDAD_MENSAJES_SIN_FORMATO_TELEFONO,
      noAceptados: e.CANTIDAD_MENSAJES_NO_ACEPTADOS,
      hora: e.HORA_ENVIO,
      fechaEnvio: e.FECHA_ENVIO,
      fechaCorresponde: e.FECHA_CORRESPONDE,
      estado: e.ESTADO
    }
  };
});

}

// =====================
// MAPA DE ESTADOS DE TASY
// =====================
const ESTADOS_TASY = {
  "A": "Aguardando",
  "AC": "Aguardando consulta",
  "AD": "Atendido",
  "B": "Bloqueada",
  "C": "Cancelada",
  "CN": "Confirmada",
  "E": "Executada",
  "L": "Libre",
  "LF": "Libre forzado",
  "N": "Normal",
  "O": "En consulta",
  "PA": "Pre-agenda",
  "PC": "Pre-agenda confirmada",
  "R": "Reservada",
  "RV": "Revisar",
  "S": "Suspendido"
};
const ESTADOS_MENSAJERIA = {
    1: "Aceptado por Meta",
    2: "Enviado",
    3: "Recibido",
    4: "Leído",
    5: "Confirmado (Agenda)",
    6: "Cancelado por usuario",
    7: "Cancelado por sistema",
    8: "Revisar",
    9: "Fallo en envío",
    10: "Eliminado",
    11: "No aceptado por Meta"
};

// Devuelve el

// =====================
// FULLCALENDAR
// =====================
document.addEventListener("DOMContentLoaded", function () {
  // === Cargar opciones del filtro de estados ===
const filtroEstado = document.getElementById("filtroEstado");
Object.keys(ESTADOS_TASY).forEach(cod => {
    const opt = document.createElement("option");
    opt.value = cod;
    opt.textContent = `${cod} - ${ESTADOS_TASY[cod]}`;
    filtroEstado.appendChild(opt);
});

  
const calendarEl = document.getElementById("calendar");

const calendar = new FullCalendar.Calendar(calendarEl, {
    themeSystem: 'bootstrap5',          // ACTIVAR BOOTSTRAP
    initialView: "dayGridMonth",
    locale: "es",
    height: "auto",
    contentHeight: 600,

    headerToolbar: {                     // Toolbar Bootstrap
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay"
    },

    buttonText: {
      today: "Hoy",
      month: "Mes",
      week: "Semana",
      day: "Día",
      prev: "Anterior",
      next: "Siguiente"
    },

    // Carga dinámica según rango de fechas
    events: async function(info, successCallback, failureCallback) {
        const start = formatDate(info.start);
        const end = formatDate(info.end);

        try {
            const eventos = await fetchEnvios(start, end);
            successCallback(eventos);
        } catch (error) {
            console.error("Error al cargar eventos:", error);
            failureCallback(error);
        }
    },
    eventContent: function(info) {
    let extra = info.event.extendedProps.extra || "";

    return {
        html: `
        <div class="fc-event-title">${info.event.title}</div>
        ${extra ? `<div class="fc-event-extra">${extra}</div>` : ""}
        `
    };
    },

    eventClick: async function(info) {
    info.jsEvent.preventDefault();

    const p = info.event.extendedProps;

   // === Mapeo de tipo de mensaje ===
    const tiposMensaje = {
        1: "Mensaje de recordatorio de consulta (72hs)",
        2: "Mensaje de recordatorio de examen (72hs)",
        3: "Lista de espera",
        4: "Turnos de consulta no contestados (24hs)",
        5: "Turnos de examen no contestados (24hs)",
        6: "Turnos de lista de espera no contestados (24hs)"
    };
    
// === Detalle del envío ===
window.idEnvioActual = info.event.id;
window.tipoMensajeActual = p.tipo;
window.tipoMensajeTexto = tiposMensaje[p.tipo];
window.fechaEnvioActual = p.fechaEnvio;  // ← guardamos la fecha

    // === Detalle del envío ===
    document.getElementById("contenido-detalle").innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>ID del envío:</strong> ${info.event.id}</p>
                <p><strong>Tipo:</strong> ${tiposMensaje[p.tipo]}</p>
                <p><strong>Template:</strong> ${p.template}</p>
                <p><strong>Estado:</strong> ${p.estado}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Total:</strong> ${p.total}</p>
                <p><strong>Enviados:</strong> ${p.enviados}</p>
                <p><strong>No aceptados:</strong> ${p.noAceptados}</p>
                <p><strong>Sin tel / paciente:</strong> ${p.sinTel} / ${p.sinPaciente}</p>
            </div>
        </div>
        <hr>
        <p><strong>Fecha envío:</strong> ${p.fechaEnvio}</p>
        <p><strong>Hora:</strong> ${p.hora}</p>
        <p><strong>Fecha corresponde:</strong> ${p.fechaCorresponde}</p>
    `;

    document.getElementById("detalle-evento").style.display = "block";


    // === Detalle de turnos ===
const url =
    `{{ route('mensajeria.api.detalle') }}?id_envio=${info.event.id}`;
        const contenedor = document.getElementById("contenido-turnos");
    contenedor.innerHTML = "<p>Cargando...</p>";

    try {
        const resp = await fetch(url);
        const turnos = await resp.json();

        if (!turnos.length) {
            contenedor.innerHTML = "<p>No hay turnos asociados.</p>";
            document.getElementById("detalle-turnos").style.display = "block";
            return;
        }

        // Guardar en memoria para filtros
        window.turnosOriginal = turnos;

        renderTablaTurnos(turnos);

        document.getElementById("detalle-turnos").style.display = "block";

    } catch (error) {
        console.error("Error cargando turnos:", error);
        contenedor.innerHTML = "<p>Error al cargar los turnos.</p>";
    }
}


  });
  function renderTablaTurnos(turnos) {
    let html = `
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Estado</th>
                    <th>Estado Agenda</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Teléfono</th>
                    <th>Paciente</th>
                    <th>DNI</th>
                    <th>Secuencia</th>
                    <th>Agenda</th>
                </tr>
            </thead>
            <tbody>
    `;
    function nombreEstadoTasy(cod) {
    return ESTADOS_TASY[cod] || cod || "-";
}

    function nombreEstadoMensaje(id) {
        return ESTADOS_MENSAJERIA[id] || id || "-";
    }

    turnos.forEach(t => {
        html += `
            <tr>
                <td>${nombreEstadoMensaje(t.ESTADO)}</td>
                <td>${nombreEstadoTasy(t.ESTADO_AGENDA)}</td>
                <td>${t.FECHA || "-"}</td>
                <td>${t.HORA || "-"}</td>
                <td>${t.TELEFONO || "-"}</td>
                <td>${t.NOMBREPACIENTE || "-"}</td>
                <td>${t.DNIPACIENTE || "-"}</td>
                <td>${t.NRSEQUENCIA || "-"}</td>
                <td>${t.DESCRIPCIONAGENDA || "-"}</td>
            </tr>
        `;
    });

    html += "</tbody></table>";

    document.getElementById("contenido-turnos").innerHTML = html;
}
["filtroNombre", "filtroDni", "filtroTel", "filtroFecha", "filtroAgenda", "filtroEstado", "filtroEstadoMensaje"].forEach(id => {
  document.getElementById(id).addEventListener("input", filtrarTurnos);
});

function filtrarTurnos() {

    const estadoMsg = document.getElementById("filtroEstadoMensaje").value;
    const estado = document.getElementById("filtroEstado").value;
    const nombre = document.getElementById("filtroNombre").value.toLowerCase();
    const dni = document.getElementById("filtroDni").value.toLowerCase();
    const tel = document.getElementById("filtroTel").value.toLowerCase();
    const fecha = document.getElementById("filtroFecha").value;
    const agenda = document.getElementById("filtroAgenda").value.toLowerCase();

    const filtrados = window.turnosOriginal.filter(t => {
        return (
            (t.NOMBREPACIENTE || "").toLowerCase().includes(nombre) &&
            (t.DNIPACIENTE || "").toLowerCase().includes(dni) &&
            (t.TELEFONO || "").toLowerCase().includes(tel) &&
            (!fecha || t.FECHA === fecha) &&
            (!estado || t.ESTADO_AGENDA === estado) &&
            (!estadoMsg || String(t.ESTADO) === estadoMsg) &&
            (t.DESCRIPCIONAGENDA || "").toLowerCase().includes(agenda)
        );
    });

    renderTablaTurnos(filtrados);
}


  calendar.render();
});

document.getElementById("btnExcel").addEventListener("click", function () {
    const tabla = document.querySelector("#contenido-turnos table");

    if (!tabla) {
        alert("No hay datos para exportar.");
        return;
    }

    // Convertir tabla HTML → hoja Excel
    const workbook = XLSX.utils.table_to_book(tabla, { sheet: "Turnos" });
      // Nombre del archivo con ID del envío
      const filename = `turnos_envio_${window.idEnvioActual || 'sin_id'}.xlsx`;

    // Descargar archivo
    XLSX.writeFile(workbook, filename);
});

</script>

@endsection
// Bytt ut disse med dine egne verdier fra Supabase.
const SUPABASE_URL = "https://tlimyuwfgbvmiknyinjs.supabase.co";
const SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InRsaW15dXdmZ2J2bWlrbnlpbmpzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODIwNjU0MzQsImV4cCI6MjA5NzY0MTQzNH0.0DEvI84kwfCgGMUFMFBW54S8uYowxAW_-6yEXn4yIwM";

const client = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

const form = document.querySelector("#response-form");
const statusEl = document.querySelector("#status");
const responsesEl = document.querySelector("#responses");

let aktiviteter = [];

async function loadAktiviteter() {
  const { data, error } = await client
    .from("aktiviteter")
    .select("*")
    .order("id", { ascending: true });

  if (error) {
    console.error(error);
    statusEl.textContent = "Kunne ikke laste aktiviteter.";
    return;
  }

  aktiviteter = data;
  renderActivityCheckboxes();
}

function renderActivityCheckboxesx() {
  const container = document.querySelector("#activity-options");

  container.innerHTML = aktiviteter
    .map(
      (a) => `
        <label>
          <input type="checkbox" name="aktiviteter" value="${a.id}">
          ${escapeHtml(a.aktivitet)}
        </label>
      `
    )
    .join("");
}


function renderActivityCheckboxes() {
  const container = document.querySelector("#activity-options");

  container.innerHTML = aktiviteter
    .map(
      (a) => `
          <label class="activity-option">
              <input type="checkbox" name="aktiviteter" value="${a.id}" ${a.id == 3 ? "disabled" : ""}>
              <span>${escapeHtml(a.aktivitet)}</span>
              <span class="info" title="${escapeHtml(a.beskrivelse ?? "")}">ⓘ</span>
          </label>
      `
    )
    .join("");
}


form.addEventListener("submit", async (event) => {
  event.preventDefault();

  statusEl.textContent = "Lagrer...";

  const formData = new FormData(form);
  const navn = formData.get("name").trim();

  const valgteAktiviteter = formData
    .getAll("aktiviteter")
    .map((id) => Number(id));

  if (!navn || valgteAktiviteter.length === 0) {
    statusEl.textContent = "Fyll inn navn og velg minst én dato.";
    return;
  }

  const { data: gjest, error: gjestError } = await client
    .from("gjest")
    .insert({ navn })
    .select()
    .single();

  if (gjestError) {
    console.error(gjestError);
    statusEl.textContent = "Problemer med å lagre. Sjekk om navnet allerede er registrert, og prøv eventuelt med suffix/etternavn.";
    return;
  }

  const paameldinger = valgteAktiviteter.map((idAktivitet) => ({
    idNavn: gjest.id,
    idAktivitet,
  }));

  const { error: paameldingError } = await client
    .from("paamelding")
    .insert(paameldinger);

  if (paameldingError) {
    console.error(paameldingError);
    statusEl.textContent = "Kunne ikke lagre påmelding.";
    return;
  }

  showWelcome(navn);

  statusEl.textContent = "Påmeldingen er lagret!";
  form.reset();

  await loadResponses();
});

async function loadResponses() {
  responsesEl.innerHTML = "<p>Laster påmeldinger...</p>";

  const { data, error } = await client
    .from("gjest")
    .select(`
      id,
      navn,
      paamelding (
        idAktivitet
      )
    `)
    .order("id", { ascending: true });

  if (error) {
    console.error(error);
    responsesEl.innerHTML = "<p>Kunne ikke laste påmeldinger.</p>";
    return;
  }

  renderMatrix(data);
}

function renderMatrix(gjester) {
  if (!gjester.length) {
    responsesEl.innerHTML = "<p>Ingen påmeldinger ennå.</p>";
    return;
  }

  const rows = gjester
    .map((gjest) => {
      const valgte = new Set(gjest.paamelding.map((p) => p.idAktivitet));

      const cells = aktiviteter
        .map((aktivitet) => {
          return `<td class="alignMiddle">${valgte.has(aktivitet.id) ? "✓" : ""}</td>`;
        })
        .join("");

      return `
        <tr>
          <td><strong>${escapeHtml(gjest.navn)}</strong></td>
          ${cells}
        </tr>
      `;
    })
    .join("");

  responsesEl.innerHTML = `
    <table class="responses-table">
      <thead>
        <tr>
          <th>Navn</th>
          ${aktiviteter.map((a) => `<th>${escapeHtml(a.aktivitet)}</th>`).join("")}
        </tr>
      </thead>
      <tbody>
        ${rows}
      </tbody>
    </table>
  `;
}

function escapeHtml(value) {
  return String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

async function init() {
  await loadAktiviteter();
  await loadResponses();
}

function showWelcome(navn) {
  document.getElementById("welcome-text").textContent =
    `Gleder meg til å se deg, ${navn}!`;

  document.getElementById("welcome-modal").classList.remove("hidden");
}

document.getElementById("close-modal").onclick = () => {
  document.getElementById("welcome-modal").classList.add("hidden");
};

init();
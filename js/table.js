import { getTrucks } from "./api.js";
import { openEditModal } from "./modal.js";

let trucksData = [];
let filteredData = [];

let currentSort = {
    column: null,
    direction: "asc"
};

let currentView = "active";

// 🔥 nový date filter
let dateFrom = null;
let dateTo = null;

// STATUS LABELS
const statusLabels = {
    on_way: "🚚 Na cestě",
    waiting_load: "⏳ Čeká na nakládku",
    at_ramp: "🏭 Na rampě",
    loaded: "✅ Naloženo",
    cancelled: "❌ Storno"
};

// LOAD
export async function loadData() {
    trucksData = await getTrucks();
    initFilters();
    initSearch();
    initSorting();
    applyFilter();
}

// FILTER BUTTONS
function initFilters() {
    document.getElementById("activeBtn").onclick = () => {
        currentView = "active";
        setActiveButton("activeBtn");
        applyFilter();
    };

    document.getElementById("historyBtn").onclick = () => {
        // 🔥 místo filtru otevře modal
        openHistoryModal();
    };
}

function setActiveButton(id) {
    document.querySelectorAll(".filter-bar .filter-btn").forEach(btn => {
        btn.classList.remove("active");
    });

    document.getElementById(id).classList.add("active");
}

// 🔥 HISTORY MODAL
function openHistoryModal() {
    document.getElementById("historyModal").style.display = "block";
}

window.closeHistoryModal = function () {
    document.getElementById("historyModal").style.display = "none";
};

// 🔥 APPLY DATE FILTER
window.applyDateFilter = function () {
    dateFrom = document.getElementById("dateFrom").value;
    dateTo = document.getElementById("dateTo").value;

    currentView = "history";
    setActiveButton("historyBtn");

    closeHistoryModal();
    applyFilter();
};

// SEARCH
function initSearch() {
    const input = document.getElementById("searchInput");

    input.oninput = () => {
        applyFilter();
    };
}

// FILTER
function applyFilter() {
    const search = document
        .getElementById("searchInput")
        .value
        .toLowerCase();

    const today = new Date();

    filteredData = trucksData.filter(row => {
        if (!row.created_at) return false;

        const rowDate = new Date(row.created_at);

        // 🔥 ACTIVE = dnešek
        if (currentView === "active") {
            const isSameDay =
                rowDate.getFullYear() === today.getFullYear() &&
                rowDate.getMonth() === today.getMonth() &&
                rowDate.getDate() === today.getDate();

            if (!isSameDay) return false;
        }

        // 🔥 HISTORY = podle range
        if (currentView === "history") {
            if (dateFrom) {
                const from = new Date(dateFrom);
                if (rowDate < from) return false;
            }

            if (dateTo) {
                const to = new Date(dateTo);
                to.setHours(23, 59, 59, 999);
                if (rowDate > to) return false;
            }
        }

        // SEARCH
        return Object.values(row).some(value => {
            if (!value) return false;
            return value.toString().toLowerCase().includes(search);
        });
    });

    sortData();
    renderTable();
}

// RENDER (beze změny)
function renderTable() {
    const tbody = document.querySelector("#table tbody");
    tbody.innerHTML = "";

    filteredData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("status-" + row.status);

        if (row.status === "waiting_load" && row.queue_number === 1) {
            tr.style.border = "2px solid #22c55e";
            tr.style.fontWeight = "bold";
        }

        tr.innerHTML = `
            <td>${row.gate}</td>
            <td>${row.spz}</td>
            <td>${row.carrier}</td>
            <td>${row.info || ""}</td>
            <td>${row.feedback || ""}</td>
            <td>${formatStatus(row)}</td>
            <td>${formatDate(row.created_at)}</td>
            <td>${formatDate(row.updated_at)}</td>
        `;

        tr.addEventListener("click", () => {
            openEditModal(row);
        });

        tbody.appendChild(tr);
    });
}

// STATUS
function formatStatus(row) {
    const base = statusLabels[row.status] || row.status;

    if (row.status === "waiting_load" && row.queue_number) {
        return `${base} #${row.queue_number}`;
    }

    return base;
}

// SORT (beze změny)
function initSorting() {
    const headers = document.querySelectorAll("#table thead th");

    const columns = [
        "gate",
        "spz",
        "carrier",
        "info",
        "feedback",
        "status",
        "created_at",
        "updated_at"
    ];

    headers.forEach((th, index) => {
        th.onclick = () => {
            const column = columns[index];

            if (currentSort.column === column) {
                currentSort.direction =
                    currentSort.direction === "asc" ? "desc" : "asc";
            } else {
                currentSort.column = column;
                currentSort.direction = "asc";
            }

            sortData();
            renderTable();
        };
    });
}

function sortData() {
    const { column, direction } = currentSort;
    if (!column) return;

    filteredData.sort((a, b) => {
        let valA = a[column] || "";
        let valB = b[column] || "";

        if (column === "created_at" || column === "updated_at") {
            return direction === "asc"
                ? new Date(valA) - new Date(valB)
                : new Date(valB) - new Date(valA);
        }

        valA = valA.toString().toLowerCase();
        valB = valB.toString().toLowerCase();

        if (valA < valB) return direction === "asc" ? -1 : 1;
        if (valA > valB) return direction === "asc" ? 1 : -1;
        return 0;
    });
}

// DATE FORMAT
function formatDate(dateString) {
    if (!dateString) return "";

    const date = new Date(dateString);

    return date.toLocaleString("cs-CZ", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit"
    });
}
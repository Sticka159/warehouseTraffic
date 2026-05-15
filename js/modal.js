import { updateTruck, addTruck } from "./api.js";
import { loadData } from "./table.js";

let currentUser = null;
let editingId = null;

export function initModal(user) {
    currentUser = user;

    const btn = document.getElementById("addTruckBtn");
    const modal = document.getElementById("modal");
    const modalContent = document.querySelector(".modal-content");

    if (user.role === "admin") {
        btn.style.display = "inline-block";
    }

    btn.onclick = () => openAddModal();

    modal.addEventListener("click", () => {
        closeModal();
    });

    modalContent.addEventListener("click", (e) => {
        e.stopPropagation();
    });

    window.saveTruck = saveTruck;
    window.closeModal = closeModal;
}

// ADD
function openAddModal() {
    editingId = null;

    document.getElementById("modalTitle").innerText = "Nový kamion";

    setValues("", "", "", "", "", "waiting");
    applyRoleVisibility();

    showModal();
}

// EDIT
export function openEditModal(row) {
    editingId = row.id;

    document.getElementById("modalTitle").innerText = "Editace kamionu";

    setValues(
        row.gate,
        row.spz,
        row.carrier,
        row.info,
        row.feedback,
        row.status
    );

    applyRoleVisibility();
    showModal();
}

// VALUES
function setValues(gate, spz, carrier, info, feedback, status) {
    document.getElementById("gate").value = gate;
    document.getElementById("spz").value = spz;
    document.getElementById("carrier").value = carrier;
    document.getElementById("info").value = info;
    document.getElementById("feedback").value = feedback;
    document.getElementById("status").value = status || "waiting";
}

// ROLE
function applyRoleVisibility() {
    const adminFields = document.querySelectorAll(".admin-only");

    if (currentUser.role === "admin") {
        adminFields.forEach(el => el.style.display = "block");
    } else {
        adminFields.forEach(el => el.style.display = "none");
    }
}

// MODAL
function showModal() {
    document.getElementById("modal").style.display = "block";
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
}

// SAVE
async function saveTruck() {
    const payload = {
        info: document.getElementById("info").value,
        feedback: document.getElementById("feedback").value,
        status: document.getElementById("status").value // 🔥 vždy
    };

    if (currentUser.role === "admin") {
        payload.gate = document.getElementById("gate").value;
        payload.spz = document.getElementById("spz").value;
        payload.carrier = document.getElementById("carrier").value;
    }

    let result;

    try {
        if (editingId) {
            payload.id = editingId;
            result = await updateTruck(payload);
        } else {
            result = await addTruck(payload);
        }

        if (result.success) {
            closeModal();
            loadData();
        } else {
            alert("❌ Chyba: " + (result.error || "Unknown error"));
            console.error(result);
        }

    } catch (err) {
        console.error(err);
        alert("❌ Server error");
    }
}
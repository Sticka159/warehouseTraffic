const themeLink = document.getElementById("themeStylesheet");

const lightBtn = document.getElementById("lightBtn");
const darkBtn = document.getElementById("darkBtn");

// 🔁 načtení uloženého theme
const savedTheme = localStorage.getItem("theme");

if (savedTheme === "dark") {
    setDark();
} else {
    setLight();
}

// 🌞 LIGHT
lightBtn.onclick = () => {
    setLight();
    localStorage.setItem("theme", "light");
};

// 🌙 DARK
darkBtn.onclick = () => {
    setDark();
    localStorage.setItem("theme", "dark");
};

// ===== FUNKCE =====

function setLight() {
    themeLink.href = "/css/style.css";

    lightBtn.classList.add("active");
    darkBtn.classList.remove("active");
}

function setDark() {
    themeLink.href = "/css/altStyle.css";

    darkBtn.classList.add("active");
    lightBtn.classList.remove("active");
}
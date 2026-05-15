import { checkAuth } from "./auth.js";
import { loadData } from "./table.js";
import { initModal } from "./modal.js";

async function init() {
    const user = await checkAuth();

    if (!user) return;

    initModal(user);
    loadData();

    setInterval(loadData, 5000);
}

init();
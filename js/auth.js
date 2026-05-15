export async function checkAuth() {
    const res = await fetch("../php/checkAuth.php");
    const data = await res.json();

    if (!data.authenticated) {
        window.location.href = "login.html";
        return null;
    }

    return data; // obsahuje i role
}
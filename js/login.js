async function login() {
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    const res = await fetch("../php/login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            username,
            password
        })
    });

    const result = await res.json();

    if (result.success) {
        window.location.href = "index.html";
    } else {
        document.getElementById("error").innerText = "❌ Špatné přihlašovací údaje";
    }
}
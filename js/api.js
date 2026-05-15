export async function getTrucks() {
    const res = await fetch("../php/getData.php");
    return await res.json();
}

export async function updateTruck(payload) {
    const res = await fetch("../php/updateData.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams(payload)
    });

    return await res.json();
}

export async function addTruck(payload) {
    const res = await fetch("../php/addTruck.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams(payload)
    });

    return await res.json();
}
// Enregistrement du Service Worker pour activer l'installation sur smartphone
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js')
    .then(() => console.log("Service Worker activé !"))
    .catch(err => console.warn("Erreur Service Worker :", err));
}

// 1. Initialisation de la carte
const map = L.map('map').setView([4.05, 9.7], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

let maPosition = null;
let tousLesSignalements = {}; 
let fichierPhotoSelectionne = null;

// 2. Surveillance GPS
if ("geolocation" in navigator) {
    navigator.geolocation.watchPosition(position => {
        maPosition = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
        };
    }, err => console.warn("Erreur GPS:", err.message), { enableHighAccuracy: true });
}

function calculerDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; 
    const phi1 = lat1 * Math.PI / 180;
    const phi2 = lat2 * Math.PI / 180;
    const deltaPhi = (lat2 - lat1) * Math.PI / 180;
    const deltaLambda = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
              Math.cos(phi1) * Math.cos(phi2) *
              Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c; 
}

// --- FONCTIONS DE GESTION DE L'INTERFACE FORMULAIRE ---

function ouvrirFormulaireSignalement() {
    if (!maPosition) {
        alert("Localisation GPS en cours... Veuillez patienter ou activer votre GPS.");
        return;
    }

    // Vérification anti-doublon (30 mètres)
    for (let id in tousLesSignalements) {
        let dechet = tousLesSignalements[id];
        let dist = calculerDistance(maPosition.lat, maPosition.lng, dechet.lat, dechet.lng);
        if (dist <= 30) {
            if (confirm("Un dépôt d'ordures a déjà été signalé ici. Cliquez sur OK pour ajouter votre vote de confirmation (+1 vote).")) {
                voterPourDechet(id);
            }
            return;
        }
    }

    // Réinitialisation des champs du formulaire
    document.getElementById('fieldDescription').value = "";
    document.getElementById('fieldMairie').value = "Recherche de votre zone...";
    document.getElementById('photoPreview').style.display = "none";
    document.getElementById('photoPrompt').style.display = "block";
    fichierPhotoSelectionne = null;

    // Affichage de la boîte modale
    document.getElementById('modalSignalement').style.display = "flex";

    // Lancement du Reverse Geocoding en tâche de fond pour pré-remplir le lieu
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${maPosition.lat}&lon=${maPosition.lng}`)
    .then(res => res.json())
    .then(data => {
        let addr = data.address;
        let quartier = addr.suburb || addr.neighbourhood || "";
        let ville = addr.town || addr.city || "Cameroun";
        
        // Formatage sympa : "Ndokoti, Douala" ou "Douala V"
        let emplacementApparent = quartier ? `${quartier}, ${ville}` : ville;
        document.getElementById('fieldMairie').value = emplacementApparent;
    })
    .catch(() => {
        document.getElementById('fieldMairie').value = ""; // En cas d'erreur l'utilisateur écrit tout seul
    });
}

function fermerFormulaire() {
    document.getElementById('modalSignalement').style.display = "none";
}

function declencherCamera() {
    document.getElementById('photoCapture').click();
}

// Déclenchée dès que la photo est prise par la caméra
function afficherApercuPhoto(input) {
    if (input.files && input.files[0]) {
        fichierPhotoSelectionne = input.files[0];
        
        let reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPrompt').style.display = "none";
            let preview = document.getElementById('photoPreview');
            preview.src = e.target.result;
            preview.style.display = "block";
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Traitement final au clic sur "Envoyer le signalement"
function soumettreFormulaire() {
    let desc = document.getElementById('fieldDescription').value.trim();
    let lieuSaisi = document.getElementById('fieldMairie').value.trim();

    if (!fichierPhotoSelectionne) {
        alert("Veuillez prendre une photo du déchet pour appuyer votre signalement.");
        return;
    }
    if (lieuSaisi === "" || lieuSaisi === "Recherche de votre zone...") {
        alert("Veuillez préciser le lieu ou le quartier.");
        return;
    }

    // Sécurité : On récupère directement le formulaire HTML
    const formElement = document.getElementById('reportForm');
    let formData = new FormData(formElement);
    
    // On ajoute les coordonnées GPS validées par watchPosition
    formData.append('lat', maPosition.lat);
    formData.append('lng', maPosition.lng);

    fetch('add_report.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        // Sécurité : Si le serveur renvoie une erreur (ex: 500), on l'attrape ici
        if (!res.ok) {
            throw new Error("Le serveur a renvoyé une erreur " + res.status);
        }
        return res.text(); // On récupère d'abord en texte brut pour éviter le crash JSON
    })
    .then(text => {
        try {
            const data = JSON.parse(text); // On tente de convertir en JSON
            alert(data.message || "Signalement envoyé !");
        } catch (e) {
            // Si le PHP a renvoyé du texte ou une erreur SQL, on l'affiche pour comprendre
            alert("Réponse du serveur : " + text);
        }
        
        fermerFormulaire();
        chargerSignalements(); 
    })
    .catch(err => {
        console.error("Erreur envoi:", err);
        alert("Erreur de connexion lors de l'envoi. Vérifiez votre connexion.");
    });
}

// --- CHARGEMENT ET VOTE ---

function voterPourDechet(id) {
    let formData = new FormData();
    formData.append('id', id);
    fetch('vote_report.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => { alert(data.message); chargerSignalements(); });
}

function chargerSignalements() {
    fetch('get_reports.php')
    .then(res => res.json())
    .then(data => {
        for (let id in tousLesSignalements) { map.removeLayer(tousLesSignalements[id].marker); }
        tousLesSignalements = {}; 

        data.forEach(report => {
            let couleur = "red";
            if (report.status === "in_progress") couleur = "orange";
            else if (report.status === "cleaned") couleur = "green";
            
            let circle = L.circleMarker([report.lat, report.lng], { color: couleur, radius: 10, fillOpacity: 0.8 }).addTo(map);
            let htmlPhoto = report.photo ? `<br><img src="uploads/${report.photo}" style="width:100%; max-width:200px; border-radius:5px; margin-top:8px; display:block;">` : "";

            circle.bindPopup(`
                <b>📍 Zone : ${report.mairie}</b><br>
                <p><b>Détails :</b> ${report.description}</p>
                <small>🔥 Priorité : <b>${report.votes}</b></small>
                ${htmlPhoto}
            `);

            tousLesSignalements[report.id] = { lat: parseFloat(report.lat), lng: parseFloat(report.lng), marker: circle };
        });
    })
    .catch(err => console.error("Erreur chargement:", err));
}

chargerSignalements();
setInterval(chargerSignalements, 10000);


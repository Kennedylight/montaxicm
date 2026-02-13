const map = L.map('cartMap').setView([46.6033, 1.8883], 5);

// On charge les images de la carte (OpenStreetMap)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap'
}).addTo(map);

// --- EXERCICE PRATIQUE ---
let points = []; // Tableau pour stocker nos coordonnées

// On écoute l'événement "click" sur la carte
map.on('click', function (e) {
  const lat = e.latlng.lat;
  const lng = e.latlng.lng;

  // 1. Ajouter un marqueur là où on a cliqué
  const nouveauMarqueur = L.marker([lat, lng]).addTo(map);
  nouveauMarqueur.bindPopup(`Point enregistré !<br>Lat: ${lat.toFixed(2)}`).openPopup();

  // 2. Stocker le point dans notre tableau
  points.push([lat, lng]);

  // 3. Si on a au moins 2 points, on trace une ligne (Polyline)
  if (points.length > 1) {
    L.polyline(points, {
      color: 'blue',
      weight: 3
    }).addTo(map);
  }

  console.log("Nouveau point ajouté : ", lat, lng);
});
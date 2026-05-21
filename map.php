<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Smart Waste</title>
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; font-family: sans-serif; background-color: #f4f6f7; }
        .app-header { position: absolute; top: 0; left: 0; right: 0; height: 60px; background: linear-gradient(135deg, #2ecc71, #27ae60); color: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 10px rgba(0,0,0,0.15); z-index: 1000; }
        .app-header h1 { margin: 0; font-size: 18px; }
        #map { width: 100%; height: 100vh; padding-top: 60px; box-sizing: border-box; }
        
        .btn-report { position: absolute; bottom: 35px; left: 50%; transform: translateX(-50%); z-index: 1000; background: #e74c3c; color: white; border: none; padding: 16px 32px; font-size: 16px; font-weight: bold; border-radius: 50px; box-shadow: 0 8px 20px rgba(231, 76, 60, 0.4); cursor: pointer; }
        
        /* --- STYLE DE LA MODAL (PROPRE À TON IMAGE) --- */
        .modal-container {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 2000;
            display: none; align-items: center; justify-content: center;
        }
        .modal-content {
            background: white; width: 90%; max-width: 400px; padding: 20px;
            border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            position: relative; box-sizing: border-box;
        }
        .modal-header { display: flex; align-items: center; margin-bottom: 20px; }
        .btn-back { background: none; border: none; font-size: 24px; cursor: pointer; margin-right: 15px; }
        .modal-title { font-size: 20px; font-weight: bold; margin: 0; }
        
        /* Zone Photo en pointillés verts */
        .photo-placeholder {
            width: 100%; height: 200px; border: 2px dashed #2ecc71; border-radius: 12px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            cursor: pointer; background: #f9fbf9; overflow: hidden; margin-bottom: 15px;
        }
        .photo-placeholder img { width: 100%; height: 100%; object-fit: cover; display: none; }
        
        /* Champs de saisie stylisés */
        .input-field {
            width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd;
            border-radius: 8px; font-size: 15px; box-sizing: border-box; background: #fcfcfc;
        }
        textarea.input-field { height: 80px; resize: none; }
        
        /* Bouton envoyer vert */
        .btn-submit {
            width: 100%; background: #27ae60; color: white; border: none;
            padding: 14px; font-size: 16px; font-weight: bold; border-radius: 8px;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
    </style>
</head>
<body>

    <div class="app-header">
        <h1>Smart Waste</h1>
    </div>

    <div id="map"></div>
    
    <button class="btn-report" onclick="ouvrirFormulaireSignalement()">Signaler des déchets</button>

<div class="modal-container" id="modalSignalement">
    <div class="modal-content">
        <div class="modal-header">
            <button class="btn-back" onclick="fermerFormulaire()">←</button>
            <h3 class="modal-title">Signaler un déchet</h3>
        </div>
        
        <form id="reportForm">
            <div class="photo-placeholder" onclick="declencherCamera()">
                <div id="photoPrompt" style="text-align: center; color: #7f8c8d;">
                    <br><span style="font-size: 13px;">Cliquez pour prendre une photo</span>
                </div>
                <img id="photoPreview" alt="Aperçu du déchet">
            </div>
            
            <input type="file" id="photoCapture" name="photo" accept="image/*" capture="environment" style="display: none;" onchange="afficherApercuPhoto(this)">
            
            <input type="text" id="fieldMairie" name="mairie" class="input-field" placeholder="Quartier et Ville (ex: Ndokoti, Douala)">
            
            <textarea id="fieldDescription" name="description" class="input-field" placeholder="Description ou détails importants..."></textarea>
            
            <button type="button" class="btn-submit" onclick="soumettreFormulaire()">
                Envoyer le signalement
            </button>
        </form>
    </div>
</div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="app.js"></script>
</body>
</html>


const form = document.getElementById('inspection-form');
const typeInput = document.getElementById('typeid');
const buildingInput = document.getElementById('buildingid');
const premisesInput = document.getElementById('premisesid');
const areaInput = document.getElementById('area');
const notesInput = document.getElementById('notes');
const conditionInput = document.getElementById('condition');
//const photoInput = document.getElementById('photo');
const photoInput = document.getElementById('photo-input');
const addPhotoBtn = document.getElementById('add-photo-btn');
const photoArray = [];
const photosPreview = document.getElementById('photosPreview');
const inspectionsList = document.getElementById('inspections-list');
const userInput = document.getElementById('userid');
const recordownerInput = document.getElementById('recordownerid');
const syncButton = document.getElementById('sync-button');
const clearlistButton = document.getElementById('clear-saved-list');

// Load saved inspections from localStorage
let inspections = JSON.parse(localStorage.getItem('inspections')) || [];

displayInspections();

// Save form data locally
form.addEventListener('submit', (e) => {
    e.preventDefault();

    if (!typeInput.value || !buildingInput.value || !premisesInput.value || !conditionInput.value) {
        alert('Please fill in all fields: Type, Building, Premises and Condition');
        return;
    }

    const files = Array.from(photoInput.files);

    const saveInspection = (photos) => {
        const inspection = {
            photos: photoArray,
            id: Date.now(),
            type: typeInput.value,
            typename: typeInput.options[typeInput.selectedIndex].text,
            building: buildingInput.value,
            buildingname: buildingInput.options[buildingInput.selectedIndex].text,
            premises: premisesInput.value,
            premisesname: premisesInput.options[premisesInput.selectedIndex].text,
            area: areaInput.value,
            areaname: areaInput.options[areaInput.selectedIndex].text,
            notes: notesInput.value,
            condition: conditionInput.value,
            conditionname: conditionInput.options[conditionInput.selectedIndex].text,
            user: userInput.value,
            recordowner: recordownerInput.value,
        };

        inspections.push(inspection);
        localStorage.setItem('inspections', JSON.stringify(inspections));

        typeInput.value = '';
        buildingInput.value = '';
        premisesInput.value = 0;
        areaInput.value = '';
        notesInput.value = '';
        conditionInput.value = '';
        photoInput.value = '';
        photosPreview.innerHTML = '';

        photoArray.length = 0;

        updatePhotosPreview();

        displayInspections();

        // alert('Saved locally! Ready to sync when online.');
    };

    if (files.length > 0) {
        const readers = files.map(file => {
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result);
                reader.readAsDataURL(file);
            });
        });
        Promise.all(readers).then(images => {
            saveInspection(images);
        });
    } else {
        saveInspection([]);
    }
});

// When a new photo is selected
photoInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onloadend = () => {
        photoArray.push(reader.result); // Save image as Data URL
        updatePhotosPreview();
    };
    reader.readAsDataURL(file);
    // Reset file input for next photo
    photoInput.value = '';
});

// Display thumbnails for all added photos
function updatePhotosPreview() {
    photosPreview.innerHTML = '';
    photoArray.forEach((photo, idx) => {
        const img = document.createElement('img');
        img.src = photo;
        img.width = 100;
        img.style.marginRight = "6px";
        photosPreview.appendChild(img);
    });
}


// Display saved inspections
function displayInspections() {
    inspectionsList.innerHTML = 'Saved Inspections: <hr/>';
    inspections.forEach((insp) => {

        const div = document.createElement('div');
        const date = new Date(insp.id);

        // Get components
        const formattedDate = `${date.getDate()}/${date.getMonth()}/${date.getFullYear()} ${(date.getHours() % 12 || 12)}:${date.getMinutes().toString().padStart(2, '0')}${date.getHours() >= 12 ? 'pm' : 'am'}`;

        div.innerHTML = `
        <a href="addinspection.php?id=${insp.id}&t=${insp.type}&b=${insp.building}&pr=${insp.premises}&co=${insp.condition}"><strong>${insp.buildingname}</strong></a><br/>
        ${insp.premisesname}<br/>
        ${formattedDate}<br/>
        type: ${insp.typename}<br/>
        area: ${insp.areaname}<br/>
        notes: ${insp.notes}<br/>
        condition: ${insp.conditionname}<br/>
        id: ${insp.id}<br/>
        ${insp.photos && insp.photos.length ? insp.photos.map(p => `<img src="${p}" width="150" style="padding: 10px;"/>`).join('') : ''}
        <hr/>
        `;
        inspectionsList.appendChild(div);
    });
}

// old code from displayInspections ${insp.photo ? `<img src="${insp.photo}" width="150"/>` : ''}

// Sync data and images to the mother ship — POST to API
syncButton.addEventListener('click', () => {
    if (inspections.length === 0) {
        alert('No inspections to sync.');
        return;
    }

    inspections.forEach((insp) => {
        console.log('Would sync:', insp);

        fetch('/api/inspections.php', {
            method: 'POST',
            body: JSON.stringify(insp),
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer 21062287935eeb370e86358956428880e301050da049eeb370e86358956424a1433a9d1812bc5e5dd' }
        })

        // On success, remove from local queue
    });

    // Clear saved inspections
    inspections = [];
    localStorage.removeItem('inspections');  // Temporarily disable while testing

    typeInput.value = '';
    buildingInput.value = '';
    premisesInput.value = '';
    areaInput.value = '';
    notesInput.value = '';
    conditionInput.value = '';
    photoInput.value = '';
    photosPreview.innerHTML = '';

    photoArray.length = 0;

    displayInspections();

    alert('Sync complete.');
});

clearlistButton.addEventListener('click', () => {
    if (inspections.length === 0) {
        alert('No saved inspections.');
        return;
    }

    // Clear local inspections
    inspections = [];
    localStorage.removeItem('inspections');

    typeInput.value = '';
    buildingInput.value = '';
    premisesInput.value = 0;
    areaInput.value = '';
    notesInput.value = '';
    conditionInput.value = '';
    photoInput.value = '';
    photosPreview.innerHTML = '';

    photoArray.length = 0;

    updatePhotosPreview();

    displayInspections();

    alert('Saved inspections deleted.');
});
const form = document.getElementById('inspection-form');
const typeInput = document.getElementById('typeid');
const buildingInput = document.getElementById('buildingid');
const premisesInput = document.getElementById('premisesid');
const areaInput = document.getElementById('areaid');
const notesInput = document.getElementById('notes');
const conditionInput = document.getElementById('conditionid');
const photoInput = document.getElementById('photo-input');
const addPhotoBtn = document.getElementById('add-photo-btn');
const photoArray = [];
const photosPreview = document.getElementById('photosPreview');
const inspectionsList = document.getElementById('inspections-list');
const userInput = document.getElementById('userid');
const recordownerInput = document.getElementById('recordownerid');
const uploadButton = document.getElementById('upload-button');
const syncButton = document.getElementById('sync-button');
const clearlistButton = document.getElementById('clear-saved-list');
const idInput = document.getElementById('id');
const indexInput = document.getElementById('index');
const thisID = '';

// Load saved inspections from localStorage
/*
let inspections = JSON.parse(localStorage.getItem('inspections')) || [];
*/

// remove the saved inspection section
// displayInspections();

// Save form data to the localStorage
/*
form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!typeInput.value || !buildingInput.value || !premisesInput.value || !conditionInput.value) {
        //alert('Please fill in all fields: Type, Building, Premises and Condition');
        Swal.fire({
            title: "",
            text: "Please fill in all fields: Building, Premises, Type and Condition",
            icon: "error",
            iconColor: "#f3130bff",
            width: "350px",
            showCancelButton: false,
            confirmButtonColor: "#2d7697",
            confirmButtonText: "OK",
        })
        return;
    }
    const files = Array.from(photoInput.files);
    const saveInspection = (photos) => {
        const timestamp = idInput.value || Date.now();
        const inspection = {
            id: timestamp,
            type: typeInput.value,
            typename: typeInput.options[typeInput.selectedIndex].text,
            building: buildingInput.value,
            buildingname: buildingInput.options[buildingInput.selectedIndex].text,
            premises: premisesInput.value,
            premisesname: premisesInput.options[premisesInput.selectedIndex].text,
            areaid: areaInput.value,
            areaname: areaInput.options[areaInput.selectedIndex].text,
            notes: notesInput.value,
            conditionid: conditionInput.value,
            conditionname: conditionInput.options[conditionInput.selectedIndex].text,
            photos: photoArray,
            user: userInput.value,
            recordowner: recordownerInput.value,
        };
        const thisindex = indexInput.value;
        if (thisindex !== "") {
            inspections[thisindex] = inspection;
        } else {
            inspections.push(inspection);
        }
        
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

     // alert('Saved locally! Ready to sync when online.');
        Swal.fire({
            title: "",
            text: "Saved locally! Ready to sync with the Mother Ship.",
            icon: "success",
            iconColor: "#0cc43aff",
            width: "350px",
            showCancelButton: false,
            confirmButtonColor: "#0cc43a",
            confirmButtonText: "OK",
        }).then((result) => {
            if (result.isConfirmed) {
                displayInspections();
            }
        })

});
*/

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
/*
function displayInspections() {
    let inspections = JSON.parse(localStorage.getItem('inspections')) || [];
    inspectionsList.innerHTML = 'Saved Inspections: <hr/>';
    inspections.forEach((insp, index) => {
        const div = document.createElement('div');
        const date = new Date(insp.id);
        const formattedDate = `${date.getDate()}/${date.getMonth()}/${date.getFullYear()} ${(date.getHours() % 12 || 12)}:${date.getMinutes().toString().padStart(2, '0')}${date.getHours() >= 12 ? 'pm' : 'am'}`;
        div.innerHTML = `
        <a class="custom-link" href="#" onclick="editInspection(${index})">${insp.buildingname}</a><br/>
        ${insp.premisesname}<br/>
        date: ${formattedDate}<br/>
        area: ${insp.areaname}<br/>
        type: ${insp.typename}<br/>
        condition: ${insp.conditionname}<br/>
        notes: ${insp.notes}<br/>
        id: ${insp.id}<br/>
        index: ${index}<br/>
        ${insp.photos && insp.photos.length ? insp.photos.map(p => `<img src="${p}" width="130" style="padding: 7px;"/>`).join('') : ''}
        <hr/>
        `;
        inspectionsList.appendChild(div);
    });
}
*/

/*
function editInspection(passedinindex) {
    let inspections = JSON.parse(localStorage.getItem('inspections')) || [];
    // Find the inspection object by its index
    const insp = inspections[passedinindex];
    //alert("PremisesID: " + insp.premises);
    if (!insp) {
        Swal.fire({
            title: "",
            text: "Inspection not found.",
            icon: "error",
            iconColor: "#f3130bff",
            width: "350px",
            showCancelButton: false,
            confirmButtonColor: "#2d7697",
            confirmButtonText: "OK",
        })
        return;
    }

    // Populate form fields
    
    buildingInput.value = insp.building || '';
    areaInput.value = insp.areaid || '';
    typeInput.value = insp.type || '';
    conditionInput.value = insp.conditionid || '';
    notesInput.value = insp.notes || '';
    idInput.value = insp.id || '';
    indexInput.value = passedinindex || '0';

    // Reset photo input and preview
    photoInput.value = '';
    photosPreview.innerHTML = '';

    // Show saved images, if any
    if (insp.photos && Array.isArray(insp.photos)) {
        insp.photos.forEach((photoUrl) => {
            const img = document.createElement('img');
            img.src = photoUrl;
            img.width = 130;
            img.style.padding = '7px';
            photosPreview.appendChild(img);
            //and push into photoArray
            photoArray.push(photoUrl);
        });
    }
    updatePremisesList(insp.premises);
    //premisesInput.value = insp.premises;
}
*/

// Sync all saved data and images to the mother ship — POST to API
/*
syncButton.addEventListener('click', () => {
    if (inspections.length === 0) {
        Swal.fire({
            title: "",
            text: "No inspections to sync.",
            icon: "info",
            iconColor: "#3fc3ee",
            width: "350px",
            showCancelButton: false,
            confirmButtonColor: "#2d7697",
            confirmButtonText: "OK",
        })
        return;
    }

    inspections.forEach((insp) => {
        console.log('Syncing:', insp);

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

//    alert('Sync complete.');
        Swal.fire({
            title: "",
            text: "Sync complete.",
            icon: "success",
            iconColor: "#0cc43a",
            width: "350px",
            showCancelButton: false,
            confirmButtonColor: "#2d7697",
            confirmButtonText: "OK",
        })
});
*/

uploadButton.addEventListener('click', (e) => {
    e.preventDefault();
    if (!typeInput.value || !buildingInput.value || !premisesInput.value || !conditionInput.value) {
        Swal.fire({
            title: "",
            text: "Missing data in fields: Building, Premises, Type and Condition",
            icon: "error",
            iconColor: "#f3130bff",
            width: "350px",
            showCancelButton: false,
            confirmButtonColor: "#2d7697",
            confirmButtonText: "OK",
        })
        return;
    } else {
        const timestamp = idInput.value || Date.now();
        const files = Array.from(photoInput.files);

        if (files.length > 0) {
        const readers = files.map(file => {
            return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result);
            reader.readAsDataURL(file);
            });
        });

        Promise.all(readers).then(images => {
            photoArray.push(...images); // Spread the images into the array
        });
        }

        const bodyContent = JSON.stringify({
                id: timestamp,
                type: typeInput.value,
                building: buildingInput.value,
                premises: premisesInput.value,
                area: areaInput.value,
                notes: notesInput.value,
                condition: conditionInput.value,
                photos: photoArray,
                //photos: [],
                user: "3",
                recordowner: "28"
            });
//console.log('photoArray:', photoArray);

        const uploadURL = '/api/inspections.php'
        fetch(uploadURL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer 21062287935eeb370e86358956428880e301050da049eeb370e86358956424a1433a9d1812bc5e5dd' },
            body: bodyContent
        })
        .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
            } else {
                typeInput.value = '';
                buildingInput.value = '';
                premisesInput.value = 0;
                areaInput.value = '';
                notesInput.value = '';
                conditionInput.value = '';
                photoInput.value = '';
                photosPreview.innerHTML = '';
                photoArray.length = 0;
                Swal.fire({
                    title: "Success!",
                    text: "Inspection saved to the Mother Ship.",
                    icon: "success",
                    iconColor: "#32b10cff",
                    width: "350px",
                    showCancelButton: false,
                    confirmButtonColor: "#32b10cff",
                    confirmButtonText: "OK",
                })
                return;
            }
            return response.text(); // or response.text() if not JSON
        })
        .then(data => {
            console.log('Response from PHP:', data);
        })
        .catch(error => {
            console.error('There was a problem with the fetch:', error);
        });
    }
});

/*
clearlistButton.addEventListener('click', () => {
    if (inspections.length === 0) {
    //    alert('No saved inspections.');
        Swal.fire({
            title: "",
            text: "No saved inspections.",
            icon: "info",
            iconColor: "#3fc3ee",
            width: "350px",
            showCancelButton: false,
            confirmButtonColor: "#2d7697",
            confirmButtonText: "OK",
        })
        return;
    } else {
        Swal.fire({
            title: "",
            text: "Delete saved inspections?",
            icon: "info",
            iconColor: "#3fc3ee",
            width: "350px",
            showCancelButton: true,
            confirmButtonColor: "#32b10cff",
            cancelButtonColor: "#d30202",
            confirmButtonText: "OK",
            cancelButtonText: "Cancel",
        }).then((result) =>  {
            if (result.isConfirmed) {
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

                //alert('Saved inspections deleted.');
                Swal.fire({
                    title: "",
                    text: "Saved inspections deleted.",
                    icon: "success",
                    iconColor: "#0cc43aff",
                    width: "350px",
                    showCancelButton: false,
                    confirmButtonColor: "#0cc43aff",
                    confirmButtonText: "OK",
                })
            }
        });
        return;
    }
});
*/

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

form.addEventListener('submit', function (e) {
  e.preventDefault();

  const formData = new FormData();

  // Add your form fields
  formData.append('id', document.getElementById('id').value);
  formData.append('typeid', document.getElementById('typeid').value);
  formData.append('buildingid', document.getElementById('buildingid').value);
  formData.append('premisesid', document.getElementById('premisesid').value);
  formData.append('areaid', document.getElementById('areaid').value);
  formData.append('notes', document.getElementById('notes').value);
  formData.append('conditionid', document.getElementById('conditionid').value);
  formData.append('userid', document.getElementById('userid').value);
  formData.append('recordownerid', document.getElementById('recordownerid').value);

  // Append each photo as a file
  photoArray.forEach((file, index) => {
    const blob = dataURLToBlob(file);
    formData.append('photos[]', blob, `photo${index}.jpg`);
  });

  function dataURLToBlob(dataURL) {
    const parts = dataURL.split(';base64,');
    const byteString = atob(parts[1]);
    const arrayBuffer = new ArrayBuffer(byteString.length);
    const intArray = new Uint8Array(arrayBuffer);
    for (let i = 0; i < byteString.length; i++) {
        intArray[i] = byteString.charCodeAt(i);
    }
    return new Blob([intArray], { type: parts[0].split(':')[1] });
}

  fetch('addinspection.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(response => {
    //alert('Form submitted successfully!');
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
    console.log(response);
  })
  .catch(err => {
    console.error('Error submitting form:', err);
  });
});
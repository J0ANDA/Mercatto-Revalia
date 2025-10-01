// validar formulario
function initFormValidation() {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
}

// Preview de fotos
function previewPhotos(input) {
    const previewContainer = document.getElementById('preview-container');
    const photoPreview = document.getElementById('photo-preview');
    
    previewContainer.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        photoPreview.style.display = 'block';
        
        if (input.files.length > 10) {
            alert('10 img max.');
            input.value = '';
            photoPreview.style.display = 'none';
            return;
        }
        
        Array.from(input.files).forEach((file, index) => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`La foto ${file.name} Img 5MB max.`);
                return;
            }
            
            if (!file.type.startsWith('image/')) {
                alert(`El archivo ${file.name} Error.`);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-3 col-sm-4 col-6';
                col.innerHTML = `
                    <div class="card position-relative">
                        <img src="${e.target.result}" 
                             class="card-img-top" 
                             style="height: 120px; object-fit: cover;"
                             alt="Vista previa">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removePreview(this, ${index})">
                                    <span class="material-icons" style="font-size: 16px;">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                previewContainer.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    } else {
        photoPreview.style.display = 'none';
    }
}

//Eliminar fotos desde la vista previa
function removePreview(button, index) {
    const input = document.getElementById('fotos');
    const dt = new DataTransfer();
    const { files } = input;
    
    for(let i = 0; i < files.length; i++) {
        if(i !== index) {
            dt.items.add(files[i]);
        }
    }
    
    input.files = dt.files;
    button.closest('.col-md-3').remove();
    
    if(input.files.length === 0) {
        document.getElementById('photo-preview').style.display = 'none';
    }
}

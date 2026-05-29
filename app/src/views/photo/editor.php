<div class="editor-container">

    <div class="editor-main">

        <h2>📸 Create Photo</h2>

        <div id="message-container"></div>

        <!-- PREVIEW -->
        <div class="image-preview">

            <canvas id="editorCanvas"></canvas>

        </div>

        <!-- UPLOAD -->
        <div class="upload-section">

            <label for="imageUpload" class="upload-btn">
                📁 Choose Image
            </label>

            <input
                type="file"
                id="imageUpload"
                accept="image/jpeg,image/jpg,image/png,image/gif"
                style="display:none;"
            >

            <p id="filename" class="filename-display"></p>

        </div>

        <!-- STICKERS -->
        <div class="overlays-section">

            <h3>Select stickers:</h3>

            <div class="overlays-grid">

                <?php foreach ($overlays as $overlay): ?>

                    <div
                        class="overlay-item"
                        data-overlay="<?= htmlspecialchars($overlay['filename']) ?>"
                    >

                        <img
                            src="<?= htmlspecialchars($overlay['path']) ?>"
                            alt="Sticker"
                        >

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

        <!-- CREATE BUTTON -->
        <button id="createBtn" class="btn btn-primary" disabled>
            Create Photo
        </button>

    </div>

    <!-- SIDEBAR -->
    <div class="editor-sidebar">

        <h3>Your Photos</h3>

        <div class="user-photos">

            <?php if (empty($userPhotos)): ?>

                <p class="no-photos">No photos yet</p>

            <?php else: ?>

                <?php foreach ($userPhotos as $photo): ?>

                    <div
                        class="photo-thumbnail"
                        data-photo-id="<?= $photo['id'] ?>"
                    >

                        <img
                            src="assets/images/uploads/<?= htmlspecialchars($photo['filename']) ?>"
                            alt="Photo"
                        >

                        <div class="photo-overlay">

                            <button
                                class="delete-photo-btn"
                                data-photo-id="<?= $photo['id'] ?>"
                            >
                                🗑️
                            </button>

                        </div>

                        <div class="photo-stats">
                            ❤️ <?= $photo['likes_count'] ?>
                            💬 <?= $photo['comments_count'] ?>
                        </div>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>

</div>

<script>

/* ========================================= */
/* VARIABLES */
/* ========================================= */

let uploadedImage = null;

const canvas = document.getElementById('editorCanvas');

const ctx = canvas.getContext('2d');

const imageUpload = document.getElementById('imageUpload');

const createBtn = document.getElementById('createBtn');

const filenameDisplay = document.getElementById('filename');

let baseImage = null;

let stickers = [];

let selectedSticker = null;

let offsetX = 0;
let offsetY = 0;

/* ========================================= */
/* RESIZE CANVAS */
/* ========================================= */

function resizeCanvas(){

    const rect = canvas.parentElement.getBoundingClientRect();

    canvas.width = rect.width;

    canvas.height = rect.height;

    drawCanvas();
}

window.addEventListener('resize', resizeCanvas);

resizeCanvas();

/* ========================================= */
/* IMAGE UPLOAD */
/* ========================================= */

imageUpload.addEventListener('change', function(e){

    const file = e.target.files[0];

    if(!file) return;

    uploadedImage = file;

    filenameDisplay.textContent = file.name;

    const reader = new FileReader();

    reader.onload = function(event){

        baseImage = new Image();

        baseImage.onload = function(){

            drawCanvas();

            checkCanCreate();
        };

        baseImage.src = event.target.result;
    };

    reader.readAsDataURL(file);
});

/* ========================================= */
/* DRAW CANVAS */
/* ========================================= */

function drawCanvas(){

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if(baseImage){

        const imageRatio =
            baseImage.width / baseImage.height;

        const canvasRatio =
            canvas.width / canvas.height;

        let drawWidth;
        let drawHeight;

        if(imageRatio > canvasRatio){

            drawWidth = canvas.width;

            drawHeight = drawWidth / imageRatio;

        } else {

            drawHeight = canvas.height;

            drawWidth = drawHeight * imageRatio;
        }

        const offsetX =
            (canvas.width - drawWidth) / 2;

        const offsetY =
            (canvas.height - drawHeight) / 2;

        canvas.imageX = offsetX;
        canvas.imageY = offsetY;

        canvas.imageWidth = drawWidth;
        canvas.imageHeight = drawHeight;

        ctx.drawImage(
            baseImage,
            offsetX,
            offsetY,
            drawWidth,
            drawHeight
        );
    }

    stickers.forEach(sticker => {

        ctx.drawImage(
            sticker.img,
            sticker.x,
            sticker.y,
            sticker.width,
            sticker.height
        );
    });
}

/* ========================================= */
/* ADD STICKERS */
/* ========================================= */

document.querySelectorAll('.overlay-item').forEach(item => {

    item.addEventListener('click', function(){

        const filename = this.dataset.overlay;

        addSticker(filename);
    });
});

function addSticker(filename){

    const img = new Image();

    img.onload = function(){

        /* SMALL RESPONSIVE SIZE */

        const size = canvas.width * 0.22;

        stickers.push({

            filename: filename,

            img: img,

            x: canvas.width / 2 - size / 2,

            y: canvas.height / 2 - size / 2,

            width: size,

            height: size
        });

        drawCanvas();

        checkCanCreate();
    };

    img.src = 'assets/images/overlays/' + filename;
}

/* ========================================= */
/* SELECT STICKER */
/* ========================================= */

canvas.addEventListener('mousedown', function(e){

    const rect = canvas.getBoundingClientRect();

    const mouseX = e.clientX - rect.left;

    const mouseY = e.clientY - rect.top;

    for(let i = stickers.length - 1; i >= 0; i--){

        const s = stickers[i];

        if(

            mouseX >= s.x &&
            mouseX <= s.x + s.width &&

            mouseY >= s.y &&
            mouseY <= s.y + s.height
        ){

            selectedSticker = s;

            offsetX = mouseX - s.x;

            offsetY = mouseY - s.y;

            break;
        }
    }
});

/* ========================================= */
/* MOVE STICKER */
/* ========================================= */

canvas.addEventListener('mousemove', function(e){

    if(!selectedSticker) return;

    const rect = canvas.getBoundingClientRect();

    const mouseX = e.clientX - rect.left;

    const mouseY = e.clientY - rect.top;

    selectedSticker.x = mouseX - offsetX;

    selectedSticker.y = mouseY - offsetY;

    /* LIMITS */

    if(selectedSticker.x < 0){
        selectedSticker.x = 0;
    }

    if(selectedSticker.y < 0){
        selectedSticker.y = 0;
    }

    if(selectedSticker.x + selectedSticker.width > canvas.width){

        selectedSticker.x =
            canvas.width - selectedSticker.width;
    }

    if(selectedSticker.y + selectedSticker.height > canvas.height){

        selectedSticker.y =
            canvas.height - selectedSticker.height;
    }

    drawCanvas();
});

/* ========================================= */
/* RELEASE */
/* ========================================= */

canvas.addEventListener('mouseup', function(){

    selectedSticker = null;
});

canvas.addEventListener('mouseleave', function(){

    selectedSticker = null;
});

/* ========================================= */
/* RESIZE WITH WHEEL */
/* ========================================= */

canvas.addEventListener('wheel', function(e){

    if(!selectedSticker) return;

    e.preventDefault();

    if(e.deltaY < 0){

        selectedSticker.width += 10;
        selectedSticker.height += 10;

    } else {

        selectedSticker.width -= 10;
        selectedSticker.height -= 10;
    }

    /* MIN SIZE */

    if(selectedSticker.width < 20){

        selectedSticker.width = 20;
        selectedSticker.height = 20;
    }

    /* MAX SIZE */

    if(selectedSticker.width > 300){

        selectedSticker.width = 300;
        selectedSticker.height = 300;
    }

    drawCanvas();
});

/* ========================================= */
/* DOUBLE CLICK DELETE */
/* ========================================= */

canvas.addEventListener('dblclick', function(e){

    const rect = canvas.getBoundingClientRect();

    const mouseX = e.clientX - rect.left;

    const mouseY = e.clientY - rect.top;

    for(let i = stickers.length - 1; i >= 0; i--){

        const s = stickers[i];

        if(

            mouseX >= s.x &&
            mouseX <= s.x + s.width &&

            mouseY >= s.y &&
            mouseY <= s.y + s.height
        ){

            stickers.splice(i, 1);

            drawCanvas();

            checkCanCreate();

            break;
        }
    }
});

/* ========================================= */
/* ENABLE BUTTON */
/* ========================================= */

function checkCanCreate(){

    createBtn.disabled = !(uploadedImage && stickers.length > 0);
}

/* ========================================= */
/* CREATE PHOTO */
/* ========================================= */

createBtn.addEventListener('click', async function(){

    const formData = new FormData();

    formData.append('image', uploadedImage);

    const scaleX =
        baseImage.width / canvas.imageWidth;

    const scaleY =
        baseImage.height / canvas.imageHeight;

    const stickersData = stickers.map(sticker => {

        return {

            filename: sticker.filename,

            x:
                (sticker.x - canvas.imageX)
                * scaleX,

            y:
                (sticker.y - canvas.imageY)
                * scaleY,

            width:
                sticker.width * scaleX
        };
    });

    formData.append(
        'stickers',
        JSON.stringify(stickersData)
    );

    createBtn.disabled = true;

    createBtn.textContent = 'Creating...';

    try {

        const response = await fetch(
            'index.php?page=photo-create',
            {
                method: 'POST',

                body: formData,

                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );
        const text = await response.text();
        const data = JSON.parse(text);

        if(data.success){

            location.reload();

        } else {

            alert(data.message);
        }

    } catch(error){

        console.error(error);

    } finally {

        createBtn.disabled = false;

        createBtn.textContent = 'Create Photo';
    }
});

/* ========================================= */
/* DELETE PHOTO */
/* ========================================= */

document.querySelectorAll('.delete-photo-btn').forEach(btn => {

    btn.addEventListener('click', async function(e){

        e.stopPropagation();

        if(!confirm('Delete this photo?')){
            return;
        }

        const photoId = this.dataset.photoId;

        const response = await fetch(
            'index.php?page=photo-delete',
            {
                method: 'POST',

                headers: {
                    'Content-Type':
                        'application/x-www-form-urlencoded',

                    'X-Requested-With':
                        'XMLHttpRequest'
                },

                body: 'photo_id=' + photoId
            }
        );

        const text = await response.text();
        const data = JSON.parse(text);

        if(data.success){

            this.closest('.photo-thumbnail').remove();
        }
    });
});

</script>
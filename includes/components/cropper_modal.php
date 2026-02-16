<?php
// Default IDs (matches employees.php)
$cropModalId   = $cropModalId   ?? 'cropModal';
$cropImageId   = $cropImageId   ?? 'imageToCrop';
$cropBtnId     = $cropBtnId     ?? 'cropButton';
$cancelBtnIcon = $cancelBtnIcon ?? 'fas fa-times';
$saveBtnIcon   = $saveBtnIcon   ?? 'fas fa-check';
?>

<div class="modal fade" id="<?php echo $cropModalId; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-crop"></i> Crop Photo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="crop-container" style="width: 100%; max-height: 500px; display: flex; justify-content: center; background: #000;">
                    <img id="<?php echo $cropImageId; ?>" src="" alt="Image to crop" style="max-width: 100%; max-height: 500px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="<?php echo $cancelBtnIcon; ?>"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="<?php echo $cropBtnId; ?>">
                    <i class="<?php echo $saveBtnIcon; ?>"></i> Crop & Save
                </button>
            </div>
        </div>
    </div>
</div>
<!-- resources/views/components/upload-form.blade.php -->

<h3>{{ $title }}</h3>
<img src="{{ $imagePath }}" class="img-responsive">
<p><small>{{ $description }}</small></p>
<div class="alert alert-danger" id="{{ $errorStatusId }}" style="display: none" role="alert"></div>
<p class="text-center">
    <label for="{{ $fileInputId }}" class="pointer">
        <i class="fa fa-cloud-upload"></i> {{ $buttonText }}
    </label>
    <input id="{{ $fileInputId }}" name="image" type="file" style="display: none;" />
</p>
<div class="progress" style="display: none">
    <div id="{{ $progressBarId }}" class="progress-bar progress-bar-striped active" role="progressbar"
        aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
    </div>
</div>

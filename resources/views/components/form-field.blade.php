@props([
    'name',
    'label' => '',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'help' => null,
    'options' => [],
    'rows' => 3,
    'readonly' => false,
    'disabled' => false,
    'step' => null,
    'min' => null,
    'max' => null,
])

@php
    $actualValue = old($name, $value);
    $hasError = $errors->has($name);
    $inputClasses = 'form-control form-control-app ' . ($hasError ? 'is-invalid' : '');
    $selectClasses = 'form-select form-select-app ' . ($hasError ? 'is-invalid' : '');
@endphp

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label-app">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    @if($type === 'textarea')
        <textarea 
            name="{{ $name }}" 
            id="{{ $name }}" 
            rows="{{ $rows }}"
            class="{{ $inputClasses }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($readonly) readonly @endif
            @if($disabled) disabled @endif
            {{ $attributes }}
        >{{ $actualValue }}</textarea>

    @elseif($type === 'select')
        <select 
            name="{{ $name }}" 
            id="{{ $name }}" 
            class="{{ $selectClasses }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes }}
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach($options as $val => $optLabel)
                <option value="{{ $val }}" {{ (string) $actualValue === (string) $val ? 'selected' : '' }}>
                    {{ $optLabel }}
                </option>
            @endforeach
            {{ $slot }}
        </select>

    @else
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ $actualValue }}"
            class="{{ $inputClasses }}"
            placeholder="{{ $placeholder }}"
            @if($step !== null) step="{{ $step }}" @endif
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            @if($required) required @endif
            @if($readonly) readonly @endif
            @if($disabled) disabled @endif
            {{ $attributes }}
        >
    @endif

    @if($help && !$hasError)
        <div class="form-text text-muted small mt-1">{{ $help }}</div>
    @endif

    @error($name)
        <div class="form-error-feedback">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $message }}</span>
        </div>
    @enderror
</div>

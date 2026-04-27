@php
$design = $settings ?? null;

$primary = $design->primary_color ?? '#111827';
$secondary = $design->secondary_color ?? '#1f2937';
$background = $design->background_color ?? '#f5f7fb';
$text = $design->text_color ?? '#111827';
$card = $design->card_color ?? '#ffffff';
$button = $design->button_color ?? $primary;
$font = $design->font_family ?? 'Arial';
$radius = $design->border_radius ?? '18px';
@endphp

<style>
:root{
    --bg: {{ $background }};
    --card: {{ $card }};
    --text: {{ $text }};
    --muted: #6b7280;
    --border: #e5e7eb;

    --primary: {{ $primary }};
    --secondary: {{ $secondary }};
    --button: {{ $button }};

    --success-bg:#dcfce7;
    --success-text:#166534;
    --danger-bg:#fee2e2;
    --danger-text:#991b1b;

    --radius: {{ $radius }};
    --font: {{ $font }}, sans-serif;
}
</style>
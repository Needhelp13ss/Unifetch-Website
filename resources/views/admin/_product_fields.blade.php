<div class="grid-form">
    <div class="field"><label class="lbl" for="p_name">Name</label><input class="input" id="p_name" name="name" value="{{ old('name', $product?->name) }}" maxlength="120" required></div>
    <div class="field"><label class="lbl" for="p_category">Category</label>
        <select class="input" id="p_category" name="category" required>
            @foreach($categories as $k => $label)<option value="{{ $k }}" @selected(old('category', $product?->category) === $k)>{{ $label }}</option>@endforeach
        </select></div>
    <div class="field"><label class="lbl" for="p_price">Price (₱)</label><input class="input" id="p_price" name="price" type="number" min="1" max="9999" step="0.01" value="{{ old('price', $product?->price) }}" required></div>
    <div class="field"><label class="lbl" for="p_emoji">Emoji</label><input class="input" id="p_emoji" name="emoji" value="{{ old('emoji', $product?->emoji) }}" maxlength="10" placeholder="🍔"></div>
    <div class="field"><label class="lbl" for="p_prep">Prep time</label><input class="input" id="p_prep" name="prep_time" value="{{ old('prep_time', $product?->prep_time) }}" maxlength="20" placeholder="15-20 min"></div>
    <div class="field" style="grid-column:1 / -1"><label class="lbl" for="p_desc">Description</label><input class="input" id="p_desc" name="description" value="{{ old('description', $product?->description) }}" maxlength="255"></div>
</div>

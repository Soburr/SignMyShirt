<template>
  <div class="mode-picker">
    <p class="mode-picker__step-label">Step 2 · Write your mark</p>

    <div class="mode-picker__tabs">
      <button
        type="button"
        :class="{ active: mode === 'typed' }"
        @click="mode = 'typed'"
      >✍️ Type it</button>
      <button
        type="button"
        :class="{ active: mode === 'drawn' }"
        @click="mode = 'drawn'"
      >🖊️ Draw it</button>
    </div>

    <div class="mode-picker__pen">
      <label for="pen-color">Pen color</label>
      <input id="pen-color" type="color" v-model="color" />
      <span class="mode-picker__pen-hint">tap the square to change</span>
    </div>

    <div v-if="mode === 'typed'" class="mode-picker__typed">
      <input
        type="text"
        v-model="typedText"
        maxlength="40"
        placeholder="e.g. Tomiwa, or 'Good luck!'"
      />
    </div>

    <div v-else class="mode-picker__drawn">
      <div class="mode-picker__pad-wrap">
        <svg
          ref="pad"
          viewBox="0 0 260 120"
          class="mode-picker__pad"
          @pointerdown="startStroke"
          @pointermove="continueStroke"
          @pointerup="endStroke"
          @pointerleave="endStroke"
        >
          <path
            v-for="(d, i) in strokes"
            :key="i"
            :d="d"
            :stroke="color"
            stroke-width="2.5"
            fill="none"
            stroke-linecap="round"
          />
          <path
            v-if="currentStroke"
            :d="currentStroke"
            :stroke="color"
            stroke-width="2.5"
            fill="none"
            stroke-linecap="round"
          />
        </svg>
        <p v-if="!strokes.length && !currentStroke" class="mode-picker__pad-placeholder">
          👆 Draw here with your finger or cursor
        </p>
      </div>
      <button type="button" class="mode-picker__clear" @click="clearDrawing">
        ↺ Clear and try again
      </button>
    </div>

    <p v-if="error" class="mode-picker__error">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const mode = ref('typed');
const color = ref('#1a7a3c');
const typedText = ref('');
const error = ref('');

const strokes = reactive([]);
const currentStroke = ref('');
let drawing = false;

function startStroke(event) {
  drawing = true;
  const { x, y } = padPoint(event);
  currentStroke.value = `M${x} ${y}`;
}

function continueStroke(event) {
  if (!drawing) return;
  const { x, y } = padPoint(event);
  currentStroke.value += ` L${x} ${y}`;
}

function endStroke() {
  if (!drawing) return;
  drawing = false;
  if (currentStroke.value) strokes.push(currentStroke.value);
  currentStroke.value = '';
}

function padPoint(event) {
  const rect = event.currentTarget.getBoundingClientRect();
  return {
    x: Math.round(((event.clientX - rect.left) / rect.width) * 260),
    y: Math.round(((event.clientY - rect.top) / rect.height) * 120),
  };
}

function clearDrawing() {
  strokes.splice(0, strokes.length);
  currentStroke.value = '';
}


function getSignatureData() {
  error.value = '';

  if (mode.value === 'typed') {
    if (!typedText.value.trim()) {
      error.value = 'Type your name or a short message first.';
      return null;
    }
    return { mode: 'typed', typed_text: typedText.value.trim(), color: color.value };
  }

  if (!strokes.length) {
    error.value = 'Draw your signature first.';
    return null;
  }
  return { mode: 'drawn', drawn_path: strokes.join(' '), color: color.value };
}

defineExpose({ getSignatureData });
</script>

<style scoped>
.mode-picker { margin-top: 1.5rem; }
.mode-picker__step-label {
  font-size: 12px; font-weight: 700; letter-spacing: 0.03em;
  text-transform: uppercase; color: #1a7a3c; margin: 0 0 10px;
}
.mode-picker__tabs { display: flex; gap: 8px; margin-bottom: 14px; }
.mode-picker__tabs button {
  flex: 1; padding: 10px; border-radius: 8px; border: 1.5px solid #d8d8d8;
  background: #fff; color: #1d1d1d; font-size: 15px; cursor: pointer;
}
.mode-picker__tabs button.active { border-color: #1a7a3c; background: #f2faf5; color: #1a7a3c; font-weight: 600; }
.mode-picker__pen { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
.mode-picker__pen label { font-size: 14px; color: #1d1d1d; font-weight: 500; }
.mode-picker__pen input[type="color"] {
  width: 36px; height: 36px; padding: 0; border: 1.5px solid #d8d8d8; border-radius: 6px; cursor: pointer;
}
.mode-picker__pen-hint { font-size: 12px; color: #888; }
.mode-picker__typed input {
  width: 100%; padding: 12px; border-radius: 8px; border: 1.5px solid #d8d8d8;
  color: #1d1d1d; font-size: 15px;
}
.mode-picker__pad-wrap { position: relative; }
.mode-picker__pad {
  width: 100%; height: 120px; display: block;
  background: #fafafa; border: 2px dashed #1a7a3c; border-radius: 10px; touch-action: none;
}
.mode-picker__pad-placeholder {
  position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
  color: #999; font-size: 14px; margin: 0; pointer-events: none; text-align: center;
}
.mode-picker__clear {
  margin-top: 10px; font-size: 13px; background: none; border: none;
  color: #1a7a3c; cursor: pointer; font-weight: 500;
}
.mode-picker__error { color: #b3261e; font-size: 13px; margin-top: 10px; }
</style>
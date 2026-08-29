<template>
  <div class="shirt-canvas">
    <svg :viewBox="VIEW_BOX" class="shirt-canvas__svg" @click="handleClick">
      <defs>
        <clipPath :id="clipId">
          <path :d="shirtPath" />
        </clipPath>
      </defs>

      <path :d="shirtPath" class="shirt-canvas__outline" />

      <g :clip-path="`url(#${clipId})`">
        <g
          v-for="sig in signatures"
          :key="sig.id"
          :transform="`translate(${toX(sig.x)} ${toY(sig.y)}) rotate(${sig.rotation || 0})`"
          :opacity="overlapsDesign(sig) ? 0.55 : 1"
        >
          <text
            v-if="sig.mode === 'typed'"
            :fill="sig.color"
            font-size="10"
            text-anchor="middle"
          >{{ sig.typed_text }}</text>

          <path
            v-else
            :d="sig.drawn_path"
            :stroke="sig.color"
            stroke-width="1.6"
            fill="none"
            stroke-linecap="round"
          />
        </g>
      </g>

      <g v-if="designText || designImagePath" :transform="`translate(${toX(designX)} ${toY(designY)})`">
        <text
          v-if="designText"
          :fill="designTextColor || 'var(--text-primary)'"
          font-size="11"
          text-anchor="middle"
        >{{ designText }}</text>
        <image
          v-if="designImagePath"
          :href="designImagePath"
          :width="(designWidth / 100) * 220"
          :height="(designHeight / 100) * 260"
          :x="-(designWidth / 100) * 110"
          :y="-(designHeight / 100) * 130"
        />
      </g>
    </svg>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { SHIRT_PATHS, VIEW_BOX } from '../shirtPaths';

const props = defineProps({
  side: { type: String, required: true },
  signatures: { type: Array, default: () => [] },
  placing: { type: Boolean, default: false }, 

  designText: { type: String, default: '' },
  designTextColor: { type: String, default: '' },
  designImagePath: { type: String, default: '' },
  designX: { type: Number, default: 50 },
  designY: { type: Number, default: 50 },
  designWidth: { type: Number, default: 30 }, 
  designHeight: { type: Number, default: 20 },
});

const emit = defineEmits(['place']);

const clipId = computed(() => `shirt-clip-${props.side}`);
const shirtPath = computed(() => SHIRT_PATHS[props.side]);

function toX(pct) { return (pct / 100) * 220; }
function toY(pct) { return (pct / 100) * 260; }

function overlapsDesign(sig) {
  if (!props.designText && !props.designImagePath) return false;
  const halfW = props.designWidth / 2;
  const halfH = props.designHeight / 2;
  return (
    sig.x > props.designX - halfW && sig.x < props.designX + halfW &&
    sig.y > props.designY - halfH && sig.y < props.designY + halfH
  );
}

function handleClick(event) {
  if (!props.placing) return;

  const svg = event.currentTarget;
  const rect = svg.getBoundingClientRect();
  const xPct = ((event.clientX - rect.left) / rect.width) * 100;
  const yPct = ((event.clientY - rect.top) / rect.height) * 100;

  const point = svg.createSVGPoint();
  point.x = toX(xPct);
  point.y = toY(yPct);
  const path = svg.querySelector('.shirt-canvas__outline');

  if (!path.isPointInFill(point)) {
    return;
  }

  emit('place', { x: xPct, y: yPct });
}
</script>

<style scoped>
.shirt-canvas__svg {
  width: 100%;
  cursor: pointer;
}
.shirt-canvas__outline {
  fill: #ffffff;
  stroke: #c9c9c9;
  stroke-width: 1.6;
}
</style>
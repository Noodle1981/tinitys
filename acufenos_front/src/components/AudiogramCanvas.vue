<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ right: {}, left: {} })
  },
  activeEar: {
    type: String,
    default: 'right'
  },
  readOnly: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const canvasRef = ref(null)
const containerRef = ref(null)
const ctx = ref(null)
const hov = ref(null)

// Clinical Constants
const FREQS = [125, 250, 500, 750, 1000, 1500, 2000, 3000, 4000, 6000, 8000]
const FREQ_SHOW = new Set([125, 250, 500, 1000, 2000, 4000, 8000])
const FREQ_LABELS = { 125: '125', 250: '250', 500: '500', 750: '750', 1000: '1k', 1500: '1.5k', 2000: '2k', 3000: '3k', 4000: '4k', 6000: '6k', 8000: '8k' }
const DB_MIN = -10
const DB_MAX = 120
const PAD = { l: 54, r: 24, t: 38, b: 50 }

// Colors (matching tailwind.config.js)
const COLORS = {
  right: '#EF4444',
  left: '#3B82F6',
  speech: 'rgba(253, 224, 71, 0.15)',
  grid: 'rgba(0,0,0,0.08)',
  gridStrong: 'rgba(0,0,0,0.16)',
  text: '#111827'
}

let CW = 640
let CH = 480 // 4:3 Aspect Ratio base
let dpr = 1

const getPW = () => CW - PAD.l - PAD.r
const getPH = () => CH - PAD.t - PAD.b

const getX = (f) => {
  const index = FREQS.indexOf(Number(f))
  if (index === -1) return PAD.l
  return PAD.l + (index / (FREQS.length - 1)) * getPW()
}

const getY = (db) => {
  return PAD.t + (db - DB_MIN) / (DB_MAX - DB_MIN) * getPH()
}

const draw = () => {
  if (!ctx.value) return
  const c = ctx.value

  c.clearRect(0, 0, CW, CH)
  
  // Background
  c.fillStyle = '#FFFFFF'
  c.fillRect(0, 0, CW, CH)

  // 1. Speech Banana
  drawSpeechBanana(c)

  // 2. Grids
  drawGrid(c)

  // 3. Data
  drawEarData(c, props.modelValue.left, COLORS.left, 'left')
  drawEarData(c, props.modelValue.right, COLORS.right, 'right')

  // 4. Hover Ghost
  if (hov.value && !props.readOnly) {
    const x = getX(hov.value.freq)
    const y = getY(hov.value.db)
    const col = hov.value.ear === 'right' ? COLORS.right : COLORS.left
    c.globalAlpha = 0.4
    if (hov.value.ear === 'right') drawCircle(c, x, y, col); else drawCross(c, x, y, col)
    c.globalAlpha = 1
    
    // Label
    c.fillStyle = COLORS.text
    c.font = 'bold 10px sans-serif'
    c.textAlign = 'center'
    c.fillText(`${hov.value.freq}Hz ${hov.value.db}dB`, x, y - 15)
  }
}

const drawSpeechBanana = (c) => {
  const soundsData = [
    { hz: 250, min: 30, max: 50, sounds: ["u", "o", "m", "z"] },
    { hz: 500, min: 25, max: 45, sounds: ["a", "i", "e", "j"] },
    { hz: 1000, min: 20, max: 45, sounds: ["b", "d", "g", "r"] },
    { hz: 2000, min: 20, max: 50, sounds: ["ch", "sh", "k", "t"] },
    { hz: 4000, min: 25, max: 55, sounds: ["s", "f", "th"] },
    { hz: 6000, min: 35, max: 60, sounds: ["agudos"] }
  ]

  c.beginPath()
  soundsData.forEach((d, i) => {
    const x = getX(d.hz), y = getY(d.min)
    i === 0 ? c.moveTo(x, y) : c.lineTo(x, y)
  });
  [...soundsData].reverse().forEach((d) => c.lineTo(getX(d.hz), getY(d.max)))
  c.closePath()
  
  c.fillStyle = COLORS.speech
  c.fill()
  c.strokeStyle = 'rgba(251, 191, 36, 0.3)'
  c.lineWidth = 1
  c.stroke()

  // Letters / Sounds
  c.fillStyle = 'rgba(180, 130, 20, 0.7)'
  c.font = '500 11px sans-serif'
  c.textAlign = 'center'
  c.textBaseline = 'middle'
  soundsData.forEach(d => {
    const x = getX(d.hz)
    const y = getY((d.min + d.max) / 2)
    c.fillText(d.sounds.join(' '), x, y)
  })
}

const drawGrid = (c) => {
  // Horizontal (dB)
  for (let db = DB_MIN; db <= DB_MAX; db += 10) {
    const y = getY(db)
    c.beginPath()
    c.moveTo(PAD.l, y)
    c.lineTo(CW - PAD.r, y)
    c.strokeStyle = db === 0 ? COLORS.gridStrong : COLORS.grid
    c.lineWidth = db === 0 ? 1 : 0.5
    c.stroke()
    
    c.fillStyle = '#999'
    c.font = '10px sans-serif'
    c.textAlign = 'right'
    c.fillText(db, PAD.l - 12, y + 4)
  }

  // Vertical (Hz)
  FREQS.forEach(hz => {
    const x = getX(hz)
    const show = FREQ_SHOW.has(hz)
    c.beginPath()
    c.moveTo(x, PAD.t)
    c.lineTo(x, CH - PAD.b)
    c.strokeStyle = show ? COLORS.gridStrong : COLORS.grid
    c.lineWidth = 0.5
    c.stroke()
    
    if (show) {
      c.fillStyle = COLORS.text
      c.font = 'bold 11px sans-serif'
      c.textAlign = 'center'
      c.fillText(FREQ_LABELS[hz], x, CH - PAD.b + 20)
    }
  })
}

const drawEarData = (c, data, color, type) => {
  const freqs = Object.keys(data)
    .map(Number)
    .filter(f => data[f] !== null && data[f] !== undefined)
    .sort((a,b) => a-b)
    
  if (freqs.length === 0) return

  // Lines
  c.beginPath()
  c.strokeStyle = color
  c.lineWidth = 2
  let lastX = null, lastY = null, lastHz = null

  for (let i = 0; i < freqs.length; i++) {
    const hz = freqs[i]
    if (!FREQS.includes(hz)) continue
    const x = getX(hz), y = getY(data[hz])
    
    if (lastX === null) {
      c.moveTo(x, y)
    } else {
      // Gap Detection (Standard clinical rule: don't connect if a primary frequency is skipped)
      let hasGap = false
      for (let f of FREQ_SHOW) {
        if (f > lastHz && f < hz) { hasGap = true; break; }
      }
      if (hasGap) c.moveTo(x, y); else c.lineTo(x, y)
    }
    lastX = x; lastY = y; lastHz = hz
  }
  c.stroke()

  // Symbols
  freqs.forEach(hz => {
    if (!FREQS.includes(hz)) return
    const x = getX(hz), y = getY(data[hz])
    if (type === 'right') drawCircle(c, x, y, color); else drawCross(c, x, y, color)
  })
}

const drawCircle = (c, x, y, col) => {
  c.beginPath(); c.arc(x, y, 6, 0, Math.PI * 2)
  c.fillStyle = '#FFF'; c.fill()
  c.strokeStyle = col; c.lineWidth = 2; c.stroke()
}

const drawCross = (c, x, y, col) => {
  const s = 5
  c.beginPath(); c.arc(x, y, 6, 0, Math.PI * 2); c.fillStyle = '#FFF'; c.fill()
  c.beginPath()
  c.moveTo(x - s, y - s); c.lineTo(x + s, y + s)
  c.moveTo(x + s, y - s); c.lineTo(x - s, y + s)
  c.strokeStyle = col; c.lineWidth = 2.5; c.stroke()
}

// Interaction
const handleMouseMove = (e) => {
  if (props.readOnly) return
  const rect = canvasRef.value.getBoundingClientRect()
  const mouseX = (e.clientX - rect.left) * (CW / rect.width)
  const mouseY = (e.clientY - rect.top) * (CH / rect.height)

  // Snap Freq
  let bestF = null, minDist = Infinity
  FREQS.forEach(f => {
    const d = Math.abs(getX(f) - mouseX)
    if (d < minDist) { minDist = d; bestF = f }
  })
  
  if (minDist < 30) {
    // Snap dB (5dB steps)
    const dbRaw = DB_MIN + (mouseY - PAD.t) / getPH() * (DB_MAX - DB_MIN)
    const bestDb = Math.max(DB_MIN, Math.min(DB_MAX, Math.round(dbRaw / 5) * 5))
    hov.value = { freq: bestF, db: bestDb, ear: props.activeEar }
    draw()
  } else if (hov.value) {
    hov.value = null
    draw()
  }
}

const handleMouseClick = () => {
  if (props.readOnly || !hov.value) return
  const data = JSON.parse(JSON.stringify(props.modelValue))
  const { freq, db, ear } = hov.value

  if (data[ear][freq] === db) {
    delete data[ear][freq]
  } else {
    data[ear][freq] = db
  }

  emit('update:modelValue', data)
}

const resize = () => {
  if (!containerRef.value) return
  CW = containerRef.value.clientWidth
  CH = CW * 0.75 // 4:3 Aspect Ratio
  dpr = window.devicePixelRatio || 1
  
  canvasRef.value.width = CW * dpr
  canvasRef.value.height = CH * dpr
  canvasRef.value.style.width = CW + 'px'
  canvasRef.value.style.height = CH + 'px'
  
  ctx.value = canvasRef.value.getContext('2d')
  ctx.value.setTransform(dpr, 0, 0, dpr, 0, 0)
  draw()
}

let observer = null
onMounted(() => {
  resize()
  observer = new ResizeObserver(resize)
  observer.observe(containerRef.value)
})

onUnmounted(() => {
  if (observer) observer.disconnect()
})

watch(() => props.modelValue, draw, { deep: true })
watch(() => props.activeEar, draw)
</script>

<template>
  <div ref="containerRef" class="w-full relative bg-white rounded-xl overflow-hidden shadow-inner border border-primary-100">
    <canvas 
      ref="canvasRef" 
      @mousemove="handleMouseMove" 
      @mouseleave="hov = null; draw()"
      @click="handleMouseClick"
      class="cursor-crosshair block"
    ></canvas>
  </div>
</template>

<style scoped>
canvas {
  touch-action: none;
}
</style>

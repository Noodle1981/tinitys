import { ref, reactive } from 'vue'

export function useTinnitusAudio() {
  const ctx = ref(null)
  const masterGain = ref(null)
  const activeNodes = reactive({
    left: {},
    right: {}
  })

  const freqFromSlider = (v) => {
    const minLog = Math.log(125)
    const scale = (Math.log(8000) - minLog) / 100
    return Math.round(Math.exp(minLog + scale * v))
  }

  const speedFromSlider = (v) => {
    return parseFloat((0.3 * Math.pow(40, v / 100)).toFixed(1))
  }

  const initContext = () => {
    if (!ctx.value) {
      ctx.value = new (window.AudioContext || window.webkitAudioContext)()
      masterGain.value = ctx.value.createGain()
      masterGain.value.gain.value = 0.8
      masterGain.value.connect(ctx.value.destination)
    } else if (ctx.value.state === 'suspended') {
      ctx.value.resume()
    }
  }

  const makeNoiseBuffer = () => {
    if (!ctx.value) return null
    const bufferSize = ctx.value.sampleRate * 4
    const buffer = ctx.value.createBuffer(1, bufferSize, ctx.value.sampleRate)
    const data = buffer.getChannelData(0)
    for (let i = 0; i < bufferSize; i++) {
      data[i] = Math.random() * 2 - 1
    }
    return buffer
  }

  const stopLayer = (ear, id) => {
    const nodeSet = activeNodes[ear][id]
    if (!nodeSet) return

    nodeSet.nodes.forEach(node => {
      try { node.stop?.(0) } catch (e) {}
      try { node.disconnect() } catch (e) {}
    })
    try { nodeSet.gainNode.disconnect() } catch (e) {}
    delete activeNodes[ear][id]
  }

  const startLayer = (ear, layer) => {
    initContext()
    stopLayer(ear, layer.id)

    const freq = freqFromSlider(layer.freq)
    const vol = (layer.vol / 100) * 0.3 // Scaled for safety
    const lgain = ctx.value.createGain()
    lgain.gain.value = vol

    // Stereo Panning
    const panner = ctx.value.createStereoPanner ? ctx.value.createStereoPanner() : null
    if (panner) {
      panner.pan.value = ear === 'left' ? -1 : 1
      lgain.connect(panner)
      panner.connect(masterGain.value)
    } else {
      lgain.connect(masterGain.value)
    }

    const nodes = []
    const refs = {}

    if (layer.type === 'pure') {
      const osc = ctx.value.createOscillator()
      osc.type = 'sine'
      osc.frequency.setValueAtTime(freq, ctx.value.currentTime)
      osc.connect(lgain)
      osc.start()
      nodes.push(osc)
      refs.osc = osc
    } else if (layer.type === 'noise') {
      const src = ctx.value.createBufferSource()
      src.buffer = makeNoiseBuffer()
      src.loop = true
      const filt = ctx.value.createBiquadFilter()
      filt.type = 'bandpass'
      filt.frequency.setValueAtTime(freq, ctx.value.currentTime)
      filt.Q.value = 2.0
      src.connect(filt)
      filt.connect(lgain)
      src.start()
      nodes.push(src, filt)
      refs.filter = filt
    } else if (layer.type === 'pulse') {
      const osc = ctx.value.createOscillator()
      osc.type = 'sine'
      osc.frequency.setValueAtTime(freq, ctx.value.currentTime)
      const pg = ctx.value.createGain()
      pg.gain.value = 0.5
      const lfo = ctx.value.createOscillator()
      lfo.type = 'sine'
      lfo.frequency.setValueAtTime(speedFromSlider(layer.speed), ctx.value.currentTime)
      const lfog = ctx.value.createGain()
      lfog.gain.value = 0.5
      lfo.connect(lfog)
      lfog.connect(pg.gain)
      osc.connect(pg)
      pg.connect(lgain)
      osc.start()
      lfo.start()
      nodes.push(osc, lfo, lfog, pg)
      refs.osc = osc
      refs.lfo = lfo
    } else if (layer.type === 'sweep') {
      const osc = ctx.value.createOscillator()
      osc.type = 'sine'
      osc.frequency.setValueAtTime(freq, ctx.value.currentTime)
      const flfo = ctx.value.createOscillator()
      flfo.type = 'sine'
      flfo.frequency.setValueAtTime(speedFromSlider(layer.speed), ctx.value.currentTime)
      const flfog = ctx.value.createGain()
      flfog.gain.value = freq * 0.45
      flfo.connect(flfog)
      flfog.connect(osc.frequency)
      osc.connect(lgain)
      osc.start()
      flfo.start()
      nodes.push(osc, flfo, flfog)
      refs.osc = osc
      refs.flfo = flfo
      refs.flfog = flfog
    }

    activeNodes[ear][layer.id] = { nodes, gainNode: lgain, refs }
  }

  const updateFreq = (ear, id, val) => {
    const freq = freqFromSlider(val)
    const n = activeNodes[ear][id]
    if (!n) return
    const r = n.refs
    if (r.osc) r.osc.frequency.setTargetAtTime(freq, ctx.value.currentTime, 0.05)
    if (r.filter) r.filter.frequency.setTargetAtTime(freq, ctx.value.currentTime, 0.05)
    if (r.flfog) r.flfog.gain.setTargetAtTime(freq * 0.45, ctx.value.currentTime, 0.05)
  }

  const updateVol = (ear, id, val) => {
    const n = activeNodes[ear][id]
    if (n) n.gainNode.gain.setTargetAtTime((val / 100) * 0.3, ctx.value.currentTime, 0.05)
  }

  const updateSpeed = (ear, id, val) => {
    const spd = speedFromSlider(val)
    const n = activeNodes[ear][id]
    if (!n) return
    const r = n.refs
    if (r.lfo) r.lfo.frequency.setTargetAtTime(spd, ctx.value.currentTime, 0.05)
    if (r.flfo) r.flfo.frequency.setTargetAtTime(spd, ctx.value.currentTime, 0.05)
  }

  return {
    ctx,
    activeNodes,
    freqFromSlider,
    speedFromSlider,
    initContext,
    startLayer,
    stopLayer,
    updateFreq,
    updateVol,
    updateSpeed
  }
}

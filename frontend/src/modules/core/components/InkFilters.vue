<!-- SVG filters referenced by the ink design (ov-ink.scss) via `filter: url(#...)`. -->
<template>
  <svg class="ink-filters" aria-hidden="true" focusable="false">
    <!-- Wobbly pen outline: slow waves bend the stroke, a little grain roughens its edge. -->
    <filter
      id="ov-ink-rough"
      x="-5%"
      y="-5%"
      width="110%"
      height="110%"
      color-interpolation-filters="sRGB"
    >
      <feTurbulence
        type="fractalNoise"
        baseFrequency="0.04"
        numOctaves="2"
        seed="7"
        result="wobble"
      />
      <feDisplacementMap
        in="SourceGraphic"
        in2="wobble"
        scale="5"
        xChannelSelector="R"
        yChannelSelector="G"
        result="wobbled"
      />
      <feTurbulence
        type="fractalNoise"
        baseFrequency="0.4"
        numOctaves="1"
        seed="2"
        result="grain"
      />
      <feDisplacementMap
        in="wobbled"
        in2="grain"
        scale="0.6"
        xChannelSelector="R"
        yChannelSelector="G"
      />
    </filter>

    <!-- Same pen, different seed: the second, lighter pass over an outline. -->
    <filter
      id="ov-ink-rough-retrace"
      x="-5%"
      y="-5%"
      width="110%"
      height="110%"
      color-interpolation-filters="sRGB"
    >
      <feTurbulence
        type="fractalNoise"
        baseFrequency="0.025"
        numOctaves="2"
        seed="31"
        result="wobble"
      />
      <feDisplacementMap
        in="SourceGraphic"
        in2="wobble"
        scale="7"
        xChannelSelector="G"
        yChannelSelector="R"
      />
    </filter>

    <!-- Ink soaking into paper: frays glyph edges (icons, logo). -->
    <filter id="ov-ink-bleed" color-interpolation-filters="sRGB">
      <feTurbulence
        type="fractalNoise"
        baseFrequency="0.7"
        numOctaves="2"
        seed="4"
        result="grain"
      />
      <feDisplacementMap
        in="SourceGraphic"
        in2="grain"
        scale="1.2"
        xChannelSelector="R"
        yChannelSelector="G"
      />
    </filter>
  </svg>
</template>

<style scoped>
.ink-filters {
  position: absolute;
  width: 0;
  height: 0;
  overflow: hidden;
}
</style>

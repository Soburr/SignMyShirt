export const VIEW_BOX = '0 0 220 260';

export const SHIRT_PATHS = {
  front: 'M70 26 L95 12 Q110 22 125 12 L150 26 L182 46 L166 74 L150 62 ' +
         'L150 236 L70 236 L70 62 L54 74 L38 46 Z',
  back: 'M70 22 L150 22 L182 46 L166 74 L150 62 ' +
        'L150 236 L70 236 L70 62 L54 74 L38 46 Z',
};

/**
 *
 * @param {number} xPct
 * @param {number} yPct
 * @param {'front'|'back'} side
 * @returns {boolean}
 */
export function isInsideShirt(xPct, yPct, side) {
  const svgNS = 'http://www.w3.org/2000/svg';
  const svg = document.createElementNS(svgNS, 'svg');
  const path = document.createElementNS(svgNS, 'path');
  path.setAttribute('d', SHIRT_PATHS[side]);
  svg.appendChild(path);

  svg.style.position = 'absolute';
  svg.style.opacity = '0';
  svg.style.pointerEvents = 'none';
  document.body.appendChild(svg);

  const point = svg.createSVGPoint();
  point.x = (xPct / 100) * 220;
  point.y = (yPct / 100) * 260;

  const inside = path.isPointInFill(point);
  document.body.removeChild(svg);
  return inside;
}
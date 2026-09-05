export const VIEW_BOX = '0 0 260 220';

export const SHIRT_PATHS = {
  front: 'M95 22 Q130 34 165 22 L190 30 Q215 40 210 60 Q205 78 185 70 ' +
         'L185 200 L75 200 L75 70 Q55 78 50 60 Q45 40 70 30 Z',
  back: 'M95 20 L165 20 L190 30 Q215 40 210 60 Q205 78 185 70 ' +
        'L185 200 L75 200 L75 70 Q55 78 50 60 Q45 40 70 30 Z',
};

/**
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
  point.x = (xPct / 100) * 260;
  point.y = (yPct / 100) * 220;

  const inside = path.isPointInFill(point);
  document.body.removeChild(svg);
  return inside;
}
import assert from "node:assert/strict";
import { createRequire } from "node:module";

const require = createRequire(import.meta.url);
const {
  initialRepeaterIndex,
  nextAvailableOrder,
  replaceTemplateIndex,
} = require("../wordpress/wp-content/plugins/statek-cholupice-core/assets/admin-homepage.js");

const template = `
  <div id="row-__INDEX__" data-repeater-index="__INDEX__" aria-labelledby="label-__INDEX__">
    <label id="label-__INDEX__" for="field-__INDEX__">Otázka</label>
    <input id="field-__INDEX__" name="faq[__INDEX__][question]" aria-controls="answer-__INDEX__">
    <div id="answer-__INDEX__" data-target="field-__INDEX__"></div>
  </div>`;

const start = initialRepeaterIndex(["0", "2", "8", "not-a-number"]);
assert.equal(start, 9);

const generated = Array.from({ length: 5 }, (_, offset) =>
  replaceTemplateIndex(template, start + offset),
);
assert.equal(new Set(generated).size, 5);
generated.forEach((markup, offset) => {
  const index = String(start + offset);
  assert.ok(markup.includes(`id="row-${index}"`));
  assert.ok(markup.includes(`for="field-${index}"`));
  assert.ok(markup.includes(`aria-controls="answer-${index}"`));
  assert.ok(markup.includes(`data-target="field-${index}"`));
  assert.ok(!markup.includes("__INDEX__"));
  assert.ok(!markup.includes("__index__"));
});

let counter = initialRepeaterIndex(["0", "1", "2"]);
const liveIndexes = Array.from({ length: 5 }, () => counter++);
liveIndexes.splice(2, 1);
liveIndexes.push(counter++);
assert.equal(new Set(liveIndexes).size, liveIndexes.length);
assert.equal(liveIndexes.at(-1), 8);

assert.equal(initialRepeaterIndex(["0", "2", "8", "12"]), 13);
assert.equal(nextAvailableOrder([], 12), 1);
assert.equal(nextAvailableOrder([1], 12), 2);
assert.equal(nextAvailableOrder([1, 2, 3], 12), 4);
assert.equal(nextAvailableOrder([1, 3, 12], 12), 2);
assert.equal(nextAvailableOrder([1, 1, 2], 12), 3);
assert.equal(nextAvailableOrder(["", "invalid"], 12), 1);

console.log("Homepage admin repeater tests: OK");

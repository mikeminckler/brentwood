import { Mark } from "tiptap";
import { toggleMark, updateMark } from "tiptap-commands";

export default class CustomStyle extends Mark {
  get name() {
    return "toggleClass";
  }

  get schema() {
    return {
      attrs: {
        class: {
          default: ''
        }
      },
      inline: true,
      //group: 'inline',
      draggable: false,
      parseDOM: [
        {
          tag: "span",
          getAttrs: dom => ({
            class: dom.getAttribute('class')
          }),
        }
      ],
      toDOM: mark => ['span', {
          class: `${mark.attrs.class}`
      }, 0],
    };
  }

  commands({ type }) {
    return attrs => toggleMark(type, attrs);
  }
}

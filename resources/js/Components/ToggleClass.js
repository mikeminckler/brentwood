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
      //content: 'text*',
      //group: 'block',
      //defining: true,
      //draggable: false,
      parseDOM: [
        {
          //tag: "p",
          getAttrs: dom => ({
            class: dom.getAttribute('class')
          }),
        }
      ],
      toDOM: mark => ['p', {
          class: `${mark.attrs.class}`
      }, 0],
    };
  }

  commands({ type }) {
    return attrs => toggleMark(type, attrs);
  }
}

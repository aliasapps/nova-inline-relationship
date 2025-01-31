<template>
  <div class="card shadow-md mb-4">
    <div class="bg-30 flex p-2 border-b border-40">
      <div
        v-if="!field.singular && field.sortable"
        class="w-1/8 text-left py-2 px-2"
      >
        <span class="relationship-item-handle py-2 px-2 cursor-move">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            width="24"
            height="24"
          >
            <path
              class="heroicon-ui"
              d="M4 5h16a1 1 0 0 1 0 2H4a1 1 0 1 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 0 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 0 1 0-2z"
            ></path>
          </svg>
        </span>
      </div>

      <div class="w-5/8 flex-grow text-left py-2 px-2">
        <h4 class="font-normal text-80" v-text="label"></h4>
      </div>

      <div v-if="field.deletable" class="w-1/4 text-right">
        <button
          class="btn btn-default btn-icon btn bg-transparent hover:bg-danger text-danger hover:text-white border border-danger hover:border-transparent inline-flex items-center relative mr-3"
          title="Delete"
          @click.prevent="removeItem"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 20 20"
            class="fill-current text-0"
          >
            <path
              fill-rule="nonzero"
              d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"
            ></path>
          </svg>
        </button>
      </div>
    </div>

    <div
      v-for="(field, attrib) in fields"
      :key="attrib"
      class="nova-items-field-input-wrapper w-full"
    >
      <div v-if="field.helpText" class="help-text ml-8 mt-2">
        {{ field.helpText }}
      </div>

      <form-core-required
        v-if="field.attrib === 'core_required'"
        :field="field"
        :ref="attrib"
        :resource-id="modelId"
        :resource-name="modelKey"
        :orderType="field.parentAttribute"
      >
      </form-core-required>

      <form-core-fee-rquired
        v-else-if="field.attrib === 'core_fee'"
        :field="field"
        :ref="attrib"
        :resource-id="modelId"
        :resource-name="modelKey"
        :orderType="field.parentAttribute"
      >
      </form-core-fee-rquired>

      <form-amount-due
        v-else-if="field.attrib === 'amount_due'"
        :field="field"
        :ref="attrib"
        :resource-id="modelId"
        :resource-name="modelKey"
        :orderType="field.parentAttribute"
      >
      </form-amount-due>

      <component
        v-else
        :is="'form-' + field.component"
        :ref="attrib"
        :field="field"
        :full-width-content="true"
        :errors="errors"
        :resource-id="modelId"
        :resource-name="modelKey"
        :value="field.value"
      >
      </component>
    </div>
  </div>
</template>

<script>
import FormCoreFeeRquired from "./FormCoreFeeRquired.vue";
import FormCoreRequired from "./FormCoreRequired.vue";
import FormAmountDue from "./FormAmountDue.vue";

export default {
  name: "relationship-form-item",

  components: {
    FormCoreRequired,
    FormCoreFeeRquired,
    FormAmountDue,
  },

  props: [
    "value",
    // "label",
    "id",
    "modelId",
    "modelKey",
    "errors",
    "field",
  ],

  computed: {
    fields() {
      const formItems = _.keyBy(
        Object.keys({ ...this.value }).map((attrib) => {
          return {
            ...{
              options: {},
            },
            ...this.value[attrib].meta,
            ...{
              parentAttribute: this.field.attribute + "_" + this.id,
              helpText: this.value[attrib].meta.helpText,
              attribute:
                this.value[attrib].meta.component === "file-field"
                  ? attrib + "?" + this.id
                  : this.field.attribute + "_" + this.id + "_" + attrib, // This is needed to enable delete link for file without triggering duplicate id warning
              name: this.value[attrib].meta.singularLabel,
              deletable: this.modelId > 0, // Hide delete button if model Id is not present, i.e. new model
              attrib: attrib,
            },
          };
        }),
        "attrib"
      );

      return {
        // order_type was being put in the bottom...
        // ...this was to change the order and have it on top.
        order_type: formItems.order_type,
        ...formItems,
      };
    },

    label() {
      return this.field.singular
        ? this.field.singularLabel
        : `${this.field.singularLabel} ${this.id + 1}`;
    },
  },

  methods: {
    getValueFromChildren() {
      return _.tap(new FormData(), (formData) => {
        _(this.$refs).each((item) => {
          if (item[0].field.component === "file-field") {
            if (item[0].file) {
              formData.append(
                item[0].field.attrib,
                item[0].file,
                item[0].fileName
              );
            } else if (item[0].value) {
              formData.append(item[0].field.attrib, String(item[0].value));
            }
          } else if (item[0].field.component === "boolean-field") {
            formData.append(item[0].field.attribute, item[0].trueValue);
          } else if (item[0].field.component === "core-required") {
            // console.log({
            //   message: "core_required",
            //   attribute: item[0].field.attribute,
            //   attribute: item[0].field.attribute,
            //   value: item[0].value,
            //   trueValue: item[0].trueValue,
            // });
            formData.append(item[0].field.attribute, item[0].value ? 1 : 0);
          } else {
            item[0].fill(formData);
            // console.log({
            //   // message: "core_required",
            //   attribute: item[0].field.attribute,
            //   value: item[0].value,
            //   trueValue: item[0].trueValue,
            // });
          }
        });
      });
    },

    fill(formData, parentAttrib) {
      formData.append(`${parentAttrib}[${this.id}][modelId]`, this.modelId);
      this.getValueFromChildren().forEach((value, key) => {
        let keyParts = key.split("_");

        if (keyParts.length === 1) {
          formData.append(`${parentAttrib}[${this.id}][values][${key}]`, value);
          return;
        }

        let parentParts = parentAttrib.split("_");
        let attrib = keyParts.slice(parentParts.length + 1).join("_");
        if (attrib == "") {
          attrib = key;
        }

        formData.append(
          `${parentAttrib}[${this.id}][values][${attrib}]`,
          value
        );
      });
    },

    removeItem() {
      this.$emit("deleted", this.id);
    },
  },
};
</script>

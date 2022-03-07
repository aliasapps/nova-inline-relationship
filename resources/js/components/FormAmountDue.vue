<template>
  <default-field :field="field" :errors="errors" :show-help-text="showHelpText">
    <template slot="field">
      <!-- :id="field.name" -->
      <div class="flex -mr-px">
        <span
          class="flex items-center leading-normal rounded rounded-r-none border border-r-0 border-60 px-3 whitespace-no-wrap bg-30 text-80 text-sm font-bold"
        >
          $
        </span>
      </div>
      <input
        type="number"
        step="0.01"
        placeholder="Add Amount Due"
        class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 rounded-l-none form-control form-input form-input-bordered"
        :disabled="isDisabled"
        :class="{
          'bg-gray-300': isDisabled,
        }"
        v-model="value"
      />
    </template>
  </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from "laravel-nova";

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ["resourceName", "resourceId", "field", "orderType"],

  created() {
    Nova.$on(`${this.orderType}_order_type-change`, this.handleOrderType);

  },

  mounted() {
    this.handleOnFormUpdate(this.$parent.$children);
  },

  data() {
    return {
      // isDisabled: false,
      selectedOrderType: "",
      selectedProductTypeId: undefined,
    };
  },

  computed: {
    isDisabled() {
      let disabled = false;

      if (
        this.selectedOrderType === "RETURN" ||
        this.selectedOrderType === "PICK_UP" ||
      ) {
        disabled = true;
      }

      this.value = !disabled;

      return disabled;
    },
  },

  methods: {
    handleOrderType(data) {
      // console.log(data);
      this.selectedOrderType = data;
    },

    handleOnFormUpdate(fields) {
      let tempOrderType = "";
      // let tempProductTypeId = "";

      fields.forEach(function(field) {
        // console.log("fieldAttribute: ", field.fieldAttribute);
        if (field && field.$children && field.$children.length > 0) {
          const value = field.$children[0].field.value;
          if (field.fieldAttribute !== null && value !== null) {
            const split = field.fieldAttribute.split("_").slice(3); // to remove parent model
            const join = split.join("_");
            if (join === "order_type") {
              tempOrderType = value;
            }
          }
        }
      });

      // console.log({ tempOrderType, tempProductTypeId });

      this.selectedOrderType = tempOrderType;
      // this.selectedProductTypeId = tempProductTypeId;
    },
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.field.value || "";
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      //   console.log(this.field.attribute, this.value);

      formData.append("amount_due", this.value ?? "");
    },
  },
};
</script>

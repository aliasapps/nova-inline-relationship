<template>
  <default-field :field="field" :errors="errors" :show-help-text="showHelpText">
    <template slot="field">
      <!-- :id="field.name" -->
      <input
        type="checkbox"
        class="checkbox"
        :disabled="isDisabled"
        :class="{
          'bg-gray-300': isDisabled,
        }"
        :placeholder="field.name"
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
    Nova.$on(
      `${this.orderType}_product_type_id-change`,
      this.handleProductType
    );
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
        this.selectedOrderType === "REBUILD" ||
        this.selectedOrderType === "RETURN" ||
        this.selectedOrderType === "CHECK_AND_REPORT" ||
        this.selectedOrderType === "DROP_OFF" ||
        this.selectedOrderType === "PICK_UP"
      ) {
        disabled = true;
      }

      if (this.selectedProductTypeId && this.selectedProductTypeId == 0) {
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
    handleProductType(data) {
      // console.log(data);
      const split = data.split("-");
      // split = [product_type_id, core_required]...
      // ... essentially an enum that corresponds with...
      // ... the columns. using the core_required value to...
      // ... check if core_required for order_product is required.
      this.selectedProductTypeId = split[1];
    },
    handleOnFormUpdate(fields) {
      let tempOrderType = "";
      let tempProductTypeId = "";

      fields.forEach(function(field) {
        // console.log("fieldAttribute: ", field.fieldAttribute);
        if (field && field.$children && field.$children.length > 0) {
          const value = field.$children[0].field.value;
          if (field.fieldAttribute !== null && value !== null) {
            const split = field.fieldAttribute.split("_").slice(3); // to remove parent model
            const join = split.join("_");
            if (join === "order_type") {
              tempOrderType = value;
            } else if (join === "product_type_id") {
              // console.table(value, join);
              tempProductTypeId = value.split("-")[1];
            }
          }
        }
      });

      // console.log({ tempOrderType, tempProductTypeId });

      this.selectedOrderType = tempOrderType;
      this.selectedProductTypeId = tempProductTypeId;
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

      formData.append("core_required", this.value ? 1 : 0 || "");
    },
  },
};
</script>

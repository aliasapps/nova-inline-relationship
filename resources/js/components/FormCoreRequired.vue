<template>
  <default-field :field="field" :errors="errors" :show-help-text="showHelpText">
    <template slot="field">
      <input
        :id="field.name"
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

  props: ["resourceName", "resourceId", "field"],

  created() {
    console.log("formcore");
    // Nova.$on("order_type-change", this.handleOrderType);
  },

  data() {
    return {
      isDisabled: false,
    };
  },

  methods: {
    handleOrderType(data) {
      // console.log(data);
      if (
        data === "REBUILD" ||
        data === "RETURN" ||
        data === "CHECK_AND_REPORT"
      ) {
        this.isDisabled = true;
        this.value = false;
      } else {
        this.isDisabled = false;
        this.value = true;
      }
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
      formData.append(this.field.attribute, this.value || "");
    },
  },
};
</script>

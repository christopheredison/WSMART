/*! DataTables Bootstrap 5 integration
 * © SpryMedia Ltd - datatables.net/license
 */

(function (factory) {
  if (typeof define === "function" && define.amd) {
    // AMD
    define(["jquery", "datatables.net"], function ($) {
      return factory($, window, document);
    });
  } else if (typeof exports === "object") {
    // CommonJS
    var jq = require("jquery");
    var cjsRequires = function (root, $) {
      if (!$.fn.dataTable) {
        require("datatables.net")(root, $);
      }
    };

    if (typeof window === "undefined") {
      module.exports = function (root, $) {
        if (!root) {
          // CommonJS environments without a window global must pass a
          // root. This will give an error otherwise
          root = window;
        }

        if (!$) {
          $ = jq(root);
        }

        cjsRequires(root, $);
        return factory($, root, root.document);
      };
    } else {
      cjsRequires(window, jq);
      module.exports = factory(jq, window, window.document);
    }
  } else {
    // Browser
    factory(jQuery, window, document);
  }
})(function ($, window, document) {
  "use strict";
  var DataTable = $.fn.dataTable;

  /**
   * DataTables integration for Bootstrap 5.
   *
   * This file sets the defaults and adds options to DataTables to style its
   * controls using Bootstrap. See https://datatables.net/manual/styling/bootstrap
   * for further information.
   */

  /* Set the defaults for DataTables initialisation */
  $.extend(true, DataTable.defaults, {
    renderer: "bootstrap",
  });

  /* Default class modification */
  $.extend(true, DataTable.ext.classes, {
    container: "dt-container dt-bootstrap5",
    search: {
      input: "form-control form-control-sm",
    },
    length: {
      select: "form-select",
    },
    processing: {
      container: "dt-processing card",
    },
  });

  /* Bootstrap paging button renderer */
  DataTable.ext.renderer.pagingButton.bootstrap = function (
    settings,
    buttonType,
    content,
    active,
    disabled
  ) {
    var btnClasses = ["dt-paging-button", "page-item"];

    if (active) {
      btnClasses.push("active");
    }

    if (disabled) {
      btnClasses.push("disabled");
    }

    var li = $("<li>").addClass(btnClasses.join(" "));
    var a = $("<a>", {
      href: disabled ? null : "#",
      class: "page-link",
    })
      .html(content)
      .appendTo(li);

    return {
      display: li,
      clicker: a,
    };
  };

  DataTable.ext.renderer.pagingContainer.bootstrap = function (
    settings,
    buttonEls
  ) {
    return $("<ul/>").addClass("pagination").append(buttonEls);
  };

  DataTable.ext.renderer.layout.bootstrap = function (
    settings,
    container,
    items
  ) {
    var row = $("<div/>", {
      class: items.full ? "row justify-content-md-center" : "table-head",
    }).appendTo(container);

    var col = $("<div/>", {
      class: items.full ? "col-12" : "row",
    }).appendTo(row);

    $.each(items, function (key, val) {
      var klass;

      // Apply start / end (left / right when ltr) margins
      if (val.table) {
        klass = "table-responsive scrollbar";
      } else if (key === "start") {
        klass = "col-6 col-md-auto me-auto";
      } else if (key === "end") {
        klass = "col-6 col-md-auto";
      } else {
        klass = "col-md";
      }

      $("<div/>", {
        id: val.id || null,
        class: klass + " " + (val.className || ""),
      })
        .append(val.contents)
        .appendTo(col);
    });
  };

  return DataTable;
});

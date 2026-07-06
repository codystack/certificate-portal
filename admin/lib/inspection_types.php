<?php
/**
 * Registry of inspection types the system can GENERATE.
 * Add a new type by adding an entry here and a matching template in
 * admin/templates/<key>.php. Everything else (form, PDF, storage) is driven
 * by this definition, so new types need no schema changes.
 */

function inspection_types(): array
{
    $types = [
        "winch" => [
            "label"               => "Winch / Air Winch",
            "layout"              => "checklist",
            "owner_label"         => "Equipment Owner / Address",
            "report_title"        => "INSPECTION REPORT",
            "compliance"          => "This report complies with the requirements of the Lifting Operations and Lifting Equipment Regulations 1998",
            "reference_standard"  => "BS EN 14492-2:2006",
            "tel"                 => "08130776837, 08121182863",
            // Equipment description block (key => label)
            "equipment_fields" => [
                "description"             => "Description",
                "serial_no"               => "I.D / Serial No.",
                "swl"                     => "SWL",
                "make"                    => "Make",
                "model_no"                => "Model No.",
                "year_of_manufacture"     => "Year of Manufacture",
                "equipment_test_location" => "Equipment Test Location",
            ],
            // Winch wire-rope sub-block
            "subblock_label"  => "WINCH WIRE ROPE",
            "subblock_fields" => [
                "wr_id_no"      => "ID No.",
                "wr_swl"        => "SWL",
                "wr_dimension"  => "Dimension",
                "wr_length"     => "Length",
                "wr_tested_by"  => "Tested By",
                "wr_tested_date"=> "Tested Date",
                "wr_remark"     => "Remark",
            ],
            // Page-2 checklist: section => [components]
            "checklist" => [
                "Certification Information" => [
                    "Last Inspection Report",
                    "Maintenance Logbook",
                ],
                "Physical Marking" => [
                    "Identification Number(s)",
                ],
                "Base Fixing" => [
                    "Weld",
                    "Bolted Connection",
                    "Support Structure",
                ],
                "Winch Frame and Drum" => [
                    "Wear / Crack / Damage",
                    "Rope Guard",
                    "Lubrication",
                    "Spooling",
                ],
                "Hoist System / Drive Unit" => [
                    "Wire Rope Condition (Deformation / Corrosion)",
                    "Wire Rope Termination",
                    "Chain Condition",
                    "Emergency Stop / Braking System",
                    "Oil Level",
                    "Hoses And Fittings",
                    "Power Supply Adequacy",
                    "Connection And Wiring",
                    "Operating Control",
                ],
            ],
        ],

        "turnbuckle" => [
            "label"               => "Turnbuckle (Thorough Examination)",
            "layout"              => "items",
            "owner_label"         => "Location",
            "report_title"        => "CERTIFICATE OF THOROUGH EXAMINATION",
            "compliance"          => "This report complies with the requirements of the Lifting Operations and Lifting Equipment Regulations 1998",
            "reference_standard"  => "BS EN 4429:1987",
            "tel"                 => "07084102094, 08121182863",
            // Columns of the repeatable line-item table (key => label)
            "item_columns" => [
                "id_no"       => "ID No.",
                "qty"         => "QTY",
                "description" => "Items Description",
                "swl"         => "SWL",
                "size"        => "Size",
                "make"        => "Make",
                "remarks"     => "Remarks",
            ],
            // Specification / NDT block (key => [label, default])
            "spec_fields" => [
                "specification"     => ["label" => "Specification",     "default" => "BS EN 4429:1987"],
                "ndt_method"        => ["label" => "NDT Method",        "default" => "Visual Inspection"],
                "inspection_result" => ["label" => "Inspection Result", "default" => "No Defect was found. Fit for use."],
                "equipment_type"    => ["label" => "Equipment Type",    "default" => "N/A"],
                "pole_spacing"      => ["label" => "Pole Spacing",      "default" => "N/A"],
                "test_procedure"    => ["label" => "Test Procedure",    "default" => "GMSL/VIP/001"],
                "contrast_media"    => ["label" => "Contrast Media",    "default" => "N/A"],
                "indicator"         => ["label" => "Indicator",         "default" => "N/A"],
            ],
            "declaration" => "I hereby CERTIFY and declare that the above information is correct and the equipment has been thoroughly examined in accordance with the appropriate provisions and is free from defect as regards to its safe use as at the time of inspection.",
            "regs_footer" => "LOLER 1998 (SI 2307), PUWER 1998 (SI 2306), The Supply of Machinery (Safety) Regulations 1992 (SI 3073) and Factories Act CAP.126 L.F.N 1990",
        ],

        "visual_monkeyboard" => [
            "label"               => "Visual Inspection (Monkeyboard/Item)",
            "layout"              => "items",
            "owner_label"         => "Location",
            "report_title"        => "CERTIFICATE OF THOROUGH EXAMINATION",
            "compliance"          => "This report complies with the requirements of the Lifting Operations and Lifting Equipment Regulations 1998",
            "reference_standard"  => "BS EN 13889",
            "tel"                 => "07084102094, 08121182863",
            "item_columns" => [
                "id_no"       => "ID No.",
                "qty"         => "QTY",
                "description" => "Items Description",
                "make"        => "Make",
                "remarks"     => "Remarks",
            ],
            "spec_fields" => [
                "specification"     => ["label" => "Specification",     "default" => "BS EN 13889"],
                "ndt_method"        => ["label" => "NDT Method",        "default" => "Visual Inspection"],
                "inspection_result" => ["label" => "Inspection Result", "default" => "No Defect was found. Fit for use."],
                "equipment_type"    => ["label" => "Equipment Type",    "default" => "N/A"],
                "pole_spacing"      => ["label" => "Pole Spacing",      "default" => "N/A"],
                "test_procedure"    => ["label" => "Test Procedure",    "default" => "GMSL/VIP/001"],
                "contrast_media"    => ["label" => "Contrast Media",    "default" => "N/A"],
                "indicator"         => ["label" => "Indicator",         "default" => "N/A"],
            ],
            "declaration" => "I hereby CERTIFY and declare that the above information is correct and the equipment has been thoroughly examined in accordance with the appropriate provisions and is free from defect as regards to it safe use as at the time of inspection.",
            "regs_footer" => "LOLER 1998 (SI 2307), PUWER 1998 (SI 2306), The Supply of Machinery (Safety) Regulations 1992 (SI 3073) and Factories Act CAP.126 L.F.N 1990",
        ],

        "mpi_inspection" => [
            "label"               => "MPI Inspection (Item)",
            "layout"              => "items",
            "owner_label"         => "Equipment Owner/Address",
            "report_title"        => "CERTIFICATE OF THOROUGH EXAMINATION",
            "compliance"          => "This report complies with the requirements of the Lifting Operations and Lifting Equipment Regulations 1998",
            "reference_standard"  => "BS EN ISO 9934-1:2016",
            "tel"                 => "08130776837; 08121182863",
            "item_columns" => [
                "id_no"       => "Item/ID No.",
                "qty"         => "QTY",
                "description" => "Items Description",
                "test_area"   => "Test Area",
                "swl"         => "SWL",
                "remarks"     => "Remarks",
            ],
            "spec_fields" => [
                "name_of_rig"       => ["label" => "Name of Rig",       "default" => ""],
                "type_of_inspection"=> ["label" => "Type of Inspection Conducted", "default" => "Visual Inspection & MPI"],
                "inspection_result" => ["label" => "Inspection Result", "default" => "No Defect was found. Fit for use."],
                "equipment_type"    => ["label" => "Equipment Type",    "default" => "Bar Magnet"],
                "pole_spacing"      => ["label" => "Pole Spacing",      "default" => "100mm"],
                "test_procedure"    => ["label" => "Test Procedure",    "default" => "GMSL/NDT/MPI/001"],
                "media"             => ["label" => "Media",             "default" => "White Contrast Paint"],
                "indicator"         => ["label" => "Indicator",         "default" => "Black Magnetic Ink"],
            ],
            "declaration" => "I hereby CERTIFY and declare that the above information is correct and the equipment has been thoroughly examined in accordance with the appropriate provisions and is free from defect as regards to it safe use as at the time of inspection.",
            "regs_footer" => "LOLER 1998 (SI 2307), and Factories Act CAP.126 L.F.N 1990",
        ],

        "lever_hoist" => [
            "label"               => "Lever Hoist (Thorough Examination)",
            "layout"              => "items",
            "owner_label"         => "Location",
            "report_title"        => "CERTIFICATE OF THOROUGH EXAMINATION",
            "compliance"          => "This report complies with the requirements of the Lifting Operations and Lifting Equipment Regulations 1998",
            "reference_standard"  => "BS EN 14492-2:2019",
            "tel"                 => "08130776837; 08121182863",
            "item_columns" => [
                "id_no"       => "Item/ID No.",
                "qty"         => "QTY",
                "description" => "Items Description",
                "test_area"   => "Test Area",
                "swl"         => "SWL",
                "remarks"     => "Remarks",
            ],
            "spec_fields" => [
                "type_of_inspection"=> ["label" => "Inspection Conducted", "default" => "Visual /Functional Inspection"],
                "inspection_result" => ["label" => "Inspection Result",   "default" => "No Defect was found. Fit for use."],
                "equipment_type"    => ["label" => "Equipment Type",      "default" => "N/A"],
                "pole_spacing"      => ["label" => "Pole Spacing",        "default" => "N/A"],
                "test_procedure"    => ["label" => "Test Procedure",      "default" => "GMSL/NDT/VIP/003"],
                "contrast_media"    => ["label" => "Contrast Media",      "default" => "N/A"],
                "indicator"         => ["label" => "Indicator",           "default" => "N/A"],
            ],
            "declaration" => "I hereby CERTIFY and declare that the above information is correct and the equipment has been thoroughly examined in accordance with the appropriate provisions and is free from defect as regards to it safe use as at the time of inspection.",
            "regs_footer" => "LOLER 1998 (SI 2307), and Factories Act CAP.126 L.F.N 1990",
        ],

        "beam_trolley" => [
            "label"               => "Beam Trolley (Thorough Examination)",
            "layout"              => "loler",
            "owner_label"         => "Address of premises at which the examination was made",
            "date_label"          => "Date of Thorough Examination",
            "next_date_label"     => "Latest date next examination must be carried out",
            "report_title"        => "CERTIFICATE OF THOROUGH EXAMINATION",
            "compliance"          => "This report complies with the requirements of the Lifting Operations and Lifting Equipment Regulations 1998",
            "reference_standard"  => "BS EN 13157:2004",
            "tel"                 => "08130776837, 08121182863",
            "equipment_fields" => [
                "id_no"               => "ID No.",
                "description"         => "Description",
                "swl"                 => "Safe Working Load(s)",
                "date_of_manufacture" => "Date of Manufacture (if known)",
                "manufacturer"        => "Details of Manufacturer",
            ],
            // YES/NO questions (key => question)
            "questions" => [
                "first_exam"                => "Is this the first examination after installation or assembly at a new site or location?",
                "within_6_months"           => "Was the examination carried out within 6 months?",
                "within_12_months"          => "Was the examination carried out within 12 months?",
                "installed_correctly"       => "Has the equipment been installed correctly?",
                "examination_scheme"        => "Following an examination scheme?",
                "exceptional_circumstances" => "After the occurrence of exceptional circumstances?",
                "safe_to_operate"           => "Is this equipment safe to operate?",
            ],
            // Free-text result fields (key => [label, type, default])
            "result_fields" => [
                "date_of_report"     => ["label" => "Date of Report",                  "type" => "date", "default" => ""],
                "type_of_inspection" => ["label" => "Type of Inspection Carried Out",   "type" => "text", "default" => "Visual Inspection / Functional"],
                "inspection_result"  => ["label" => "Inspection Result",               "type" => "text", "default" => "No Defect was found. Fit for use."],
                "authenticator"      => ["label" => "Name & Position authenticating report", "type" => "text", "default" => ""],
            ],
            "declaration" => "I hereby certify and declare that the above information is correct, that the equipment has been thoroughly examined following the appropriate provisions, and that it is free from defect as regards its safe use at the time of inspection.",
            "regs_footer" => "LOLER 1998 (SI 2307), and Factories Act CAP.126 L.F.N 1990",
        ],

        "shackle" => [
            "label"               => "Shackle (Thorough Examination)",
            "layout"              => "loler",
            // Multiple equipment entries per certificate (repeatable rows),
            // instead of a single equipment_fields block like beam_trolley.
            "multi_equipment"     => true,
            "item_columns" => [
                "id_no"        => "ID No.",
                "description"  => "Description",
                "swl"          => "Safe Working Load(s)",
                "location"     => "Location on Board",
                "manufacturer" => "Details of Manufacturer",
            ],
            "owner_label"         => "Address of premises at which the examination was made",
            "date_label"          => "Date of Thorough Examination",
            "next_date_label"     => "Latest date by which next thorough examination must be carried out",
            "report_title"        => "CERTIFICATE OF THOROUGH EXAMINATION",
            "compliance"          => "This report complies with the requirements of the Lifting Operations and Lifting Equipment Regulations 1998",
            "reference_standard"  => "BS EN 13889:2003",
            "tel"                 => "07084102094, 08188490846",
            "questions" => [
                "first_exam"                => "Is this the first examination after installation or assembly at a new site or location?",
                "within_6_months"           => "Was the examination carried out within 6 months?",
                "within_12_months"          => "Was the examination carried out within 12 months?",
                "installed_correctly"       => "Has the equipment been installed correctly?",
                "examination_scheme"        => "Following an examination scheme?",
                "exceptional_circumstances" => "After the occurrence of exceptional circumstances?",
                "safe_to_operate"           => "Is this equipment safe to operate?",
            ],
            "result_fields" => [
                "date_of_report"     => ["label" => "Date of Report",                "type" => "date", "default" => ""],
                "type_of_inspection" => ["label" => "Type of Inspection Carried Out", "type" => "text", "default" => "Visual Inspection"],
                "inspection_result"  => ["label" => "Inspection Result",             "type" => "text", "default" => "No Defect was found."],
                "authenticator"      => ["label" => "Name & Position authenticating report", "type" => "text", "default" => ""],
            ],
            "declaration" => "I hereby CERTIFY and declare that the above information is correct and the equipment has been thoroughly examined in accordance with the appropriate provisions and is free from defect as regards to its safe use as at the time of inspection.",
            "regs_footer" => "LOLER 1998 (SI 2307), and Factories Act CAP.126 L.F.N 1990",
        ],

        "wire_rope_sling" => [
            "label"               => "Wire Rope Sling (Thorough Examination)",
            "layout"              => "loler",
            "multi_equipment"     => true,
            "item_columns" => [
                "id_no"                => "Item ID No",
                "qty"                  => "QTY",
                "description"          => "Item Description",
                "swl"                   => "SWL",
                "date_of_manufacture"  => "Date of Manufacture",
                "manufacturer"         => "Detail of Manufacturer",
            ],
            "owner_label"         => "Address of premises at which the examination was made",
            "date_label"          => "Date of Thorough Examination",
            "next_date_label"     => "Latest date by which next thorough examination must be carried out",
            "report_title"        => "CERTIFICATE OF THOROUGH EXAMINATION",
            "compliance"          => "This report complies with the requirements of the Lifting Operations and Lifting Equipment Regulations 1998",
            "reference_standard"  => "ASME B30.9",
            "tel"                 => "08130776837; 08121182863",
            "questions" => [
                "first_exam"                => "Is this the first examination after installation or assembly at a new site or location?",
                "within_6_months"           => "Was the examination carried out within 6 months?",
                "within_12_months"          => "Was the examination carried out within 12 months?",
                "installed_correctly"       => "Has the equipment been installed correctly?",
                "examination_scheme"        => "Following an examination scheme?",
                "exceptional_circumstances" => "After the occurrence of exceptional circumstances?",
                "safe_to_operate"           => "Is this equipment safe to operate?",
            ],
            // Extra "TEST INFORMATION" block (third-party sling test certificate),
            // folded into result_fields since that's already a generic free-text store.
            "result_fields" => [
                "date_of_report"     => ["label" => "Date of Report",                "type" => "date", "default" => ""],
                "type_of_inspection" => ["label" => "Type of Inspection Carried Out", "type" => "text", "default" => "Visual Inspection"],
                "inspection_result"  => ["label" => "Inspection Result",             "type" => "text", "default" => "No Defect was found."],
                "authenticator"      => ["label" => "Name & Position authenticating report", "type" => "text", "default" => ""],
                "test_item"          => ["label" => "Test Info: Item",               "type" => "text", "default" => "SLING"],
                "test_manufacturer"  => ["label" => "Test Info: Manufacturer",       "type" => "text", "default" => ""],
                "test_cert_no"       => ["label" => "Test Info: Certificate No",     "type" => "text", "default" => ""],
                "test_date"          => ["label" => "Test Info: Date",               "type" => "date", "default" => ""],
            ],
            "declaration" => "I hereby CERTIFY and declare that the above information is correct and the equipment has been thoroughly examined in accordance with the appropriate provisions and is free from defect as regards to it safe use as at the time of inspection.",
            "regs_footer" => "LOLER 1998 (SI 2307), and Factories Act CAP.126 L.F.N 1990",
        ],

        "drill_pipe" => [
            "label"           => "Drill Pipe Inspection",
            "layout"          => "tally",
            "report_title"    => "DRILL PIPE INSPECTION REPORT",
            "paper"           => "A3",
            "orientation"     => "landscape",
            "owner_label"     => "Rig",          // RIG -> equipment_owner column
            "location_label"  => "Location",     // LOCATION -> test_location column
            "date_label"      => "Date",
            "tel"             => "08130776837, 08121182863",
            // String/header spec fields (key => [label, default])
            "spec_fields" => [
                "dept"               => ["label" => "Dept",                "default" => "DRILLING"],
                "size"               => ["label" => "Size",                "default" => ""],
                "grade"              => ["label" => "Grade",               "default" => ""],
                "weight"             => ["label" => "Weight",              "default" => ""],
                "nom_wall_thickness" => ["label" => "Nom. Wall Thickness", "default" => ""],
                "tool_joint"         => ["label" => "Tool Joint",          "default" => ""],
                "range"              => ["label" => "Range",               "default" => ""],
                "coated"             => ["label" => "Coated?",             "default" => ""],
                "specification"      => ["label" => "Specification",       "default" => ""],
                "hardbanded"         => ["label" => "Hardbanded?",         "default" => ""],
                "others"             => ["label" => "Others",              "default" => ""],
            ],
            // Tally grid columns, grouped (ordered). Edit labels/keys here to
            // adjust the table — the form and PDF follow automatically.
            "column_groups" => [
                ["group" => "", "cols" => ["id_no" => "ID No."]],
                ["group" => "PIPE BODY", "cols" => [
                    "pb_od_wear"   => "OD Wear",
                    "pb_corrosion" => "Corrosion",
                    "pb_coat_cond" => "Coat Cond",
                    "pb_gauge"     => "Gauge",
                    "pb_slip_cuts" => "Slip Cuts",
                    "pb_min_wall"  => "Min Wall Thick",
                    "pb_bent"      => "Bent",
                    "pb_emi"       => "EMI",
                ]],
                ["group" => "BOX TOOL JOINT", "cols" => [
                    "box_od_hb"        => "OD HB",
                    "box_od_cond"      => "OD Cond",
                    "box_bevel_dia"    => "Bevel Dia",
                    "box_counter_bore" => "Counter Bore",
                    "box_seal_width"   => "Seal Width",
                    "box_shldr_width"  => "Shldr Width",
                    "box_lead_gauge"   => "Lead Gauge",
                    "box_tong_space"   => "Tong Space",
                    "box_cond"         => "Cond",
                    "box_mpi_ut"       => "MPI/UT",
                    "box_upset"        => "Upset",
                    "box_id"           => "ID",
                ]],
                ["group" => "PIN", "cols" => [
                    "pin_od_bevel_dia" => "OD Bevel Dia",
                    "pin_seal_width"   => "Seal Width",
                    "pin_length"       => "Pin Length",
                    "pin_srg_dia"      => "Pin SRG Dia",
                    "pin_srg_width"    => "Pin SRG Width",
                    "pin_lead_gauge"   => "Lead Gauge",
                    "pin_tong_space"   => "Tong Space",
                    "pin_cond"         => "Cond",
                    "pin_mp_ut"        => "MP/UT",
                    "pin_upset"        => "Upset",
                ]],
                ["group" => "", "cols" => [
                    "tally_length" => "Tally Length",
                    "remark"       => "Remark",
                    "neck_length"  => "Neck Length",
                ]],
            ],
        ],

        "drill_collar" => [
            "label"           => "Drill Collar Inspection",
            "layout"          => "tally",
            "report_title"    => "DRILL COLLAR INSPECTION REPORT",
            "paper"           => "A3",
            "orientation"     => "landscape",
            "owner_label"     => "Rig",          // RIG -> equipment_owner column
            "location_label"  => "Location",     // LOCATION -> test_location column
            "date_label"      => "Date",
            "tel"             => "08130776837, 08121182863",
            // String/header spec fields (key => [label, default]). The
            // repair_* and *_qty fields are the REMARK/CLASSIFICATION counts
            // printed near the signature block (see templates/drill_collar.php),
            // kept here rather than a new definition key since spec_fields
            // is already a generic free-text key=>label store.
            "spec_fields" => [
                "dept"               => ["label" => "Dept",                "default" => "DRILLING"],
                "size"               => ["label" => "Size",                "default" => ""],
                "onn"                => ["label" => "ONN",                 "default" => ""],
                "weight"             => ["label" => "Weight",              "default" => ""],
                "nom_wall_thickness" => ["label" => "Nom. Wall Thickness", "default" => ""],
                "tool_joint"         => ["label" => "Tool Joint",          "default" => ""],
                "range"              => ["label" => "Range",               "default" => ""],
                "coated"             => ["label" => "Coated?",             "default" => ""],
                "specification"      => ["label" => "Specification",       "default" => "DS-1 CAT 3-5"],
                "hardbanded"         => ["label" => "Hardbanded?",         "default" => ""],
                "others"             => ["label" => "Others",              "default" => ""],
                "repair_pin"         => ["label" => "Join with Pin for Repair",         "default" => "NIL"],
                "repair_box"         => ["label" => "Joint with Box for Repair",        "default" => "NIL"],
                "repair_pin_box"     => ["label" => "Joint with Pin & Box for Repair",  "default" => "NIL"],
                "repair_pin_refaced" => ["label" => "Pin Refaced / Rebeveled",          "default" => "NIL"],
                "repair_bent"        => ["label" => "Bent Pipes",                       "default" => "NIL"],
                "premium_qty"        => ["label" => "Premium Qty",         "default" => ""],
                "class2_qty"         => ["label" => "Class 2 Qty",        "default" => ""],
                "scrap_qty"          => ["label" => "Scrap Qty",           "default" => ""],
                "total_qty"          => ["label" => "Total Qty",           "default" => ""],
            ],
            // Tally grid columns, grouped (ordered). Edit labels/keys here to
            // adjust the table — the form and PDF follow automatically.
            "column_groups" => [
                ["group" => "", "cols" => ["id_no" => "ID No."]],
                ["group" => "PIPE BODY", "cols" => [
                    "pb_od_wear"       => "OD Wear",
                    "pb_corrosion_int" => "Corrosion Int",
                    "pb_corrosion_ext" => "Corrosion Ext",
                    "pb_coat_cond"     => "In. Coat Cond",
                    "pb_gauge"         => "Gauge",
                    "pb_slip_cuts"     => "Slip Cuts",
                    "pb_min_wall"      => "Min Wall Thick",
                    "pb_bent"          => "Bent",
                    "pb_emi"           => "EMI",
                ]],
                ["group" => "BOX TOOL JOINT", "cols" => [
                    "box_od"          => "OD",
                    "box_hb_od_cond"  => "HB OD Cond",
                    "box_bevel_dia"   => "Bevel Dia",
                    "box_cb_depth"    => "CB Depth",
                    "box_cb_dia"      => "CB Dia",
                    "box_seal_width"  => "Seal Width",
                    "box_shldr_width" => "Shldr Width",
                    "box_lead_gauge"  => "Lead Gauge",
                    "box_tong_space"  => "Tong Space",
                    "box_cond"        => "Cond",
                    "box_mpi_ut"      => "MPI/UT",
                    "box_upset"       => "Upset",
                    "box_id"          => "ID",
                ]],
                ["group" => "PIN", "cols" => [
                    "pin_od"          => "OD",
                    "pin_bevel_dia"   => "Bevel Dia",
                    "pin_seal_width"  => "Seal Width",
                    "pin_length"      => "Pin Length",
                    "pin_srg_dia"     => "Pin SRG Dia",
                    "pin_srg_width"   => "Pin SRG Width",
                    "pin_lead_gauge"  => "Lead Gauge",
                    "pin_tong_space"  => "Tong Space",
                    "pin_cond"        => "Cond",
                    "pin_mp_ut"       => "MP/UT",
                    "pin_upset"       => "Upset",
                ]],
                ["group" => "", "cols" => [
                    "tally_length" => "Tally Length",
                    "mfr_remark"   => "Manufacturer's Remark",
                    "neck_length"  => "Neck Length",
                    "elr"          => "ELR",
                    "slr"          => "SLR",
                ]],
            ],
        ],

        "heavy_weight" => [
            "label"           => "Heavy Weight (HWDP) Inspection",
            "layout"          => "tally",
            "report_title"    => "HEAVY WEIGHT INSPECTION REPORT",
            "paper"           => "A3",
            "orientation"     => "landscape",
            "owner_label"     => "Rig",
            "location_label"  => "Location",
            "date_label"      => "Date",
            "tel"             => "08130776837, 08121182863",
            "spec_fields" => [
                "dept"               => ["label" => "Dept",                "default" => "DRILLING"],
                "size"               => ["label" => "Size",                "default" => ""],
                "grade"              => ["label" => "Grade",               "default" => "N/A"],
                "weight"             => ["label" => "Weight",              "default" => ""],
                "nom_wall_thickness" => ["label" => "Nom. Wall Thickness", "default" => "N/A"],
                "tool_joint"         => ["label" => "Tool Joint",          "default" => ""],
                "range"              => ["label" => "Range",               "default" => ""],
                "coated"             => ["label" => "Coated?",             "default" => "NO"],
                "specification"      => ["label" => "Specification",       "default" => "DS-1 CAT 3-5"],
                "hardbanded"         => ["label" => "Hardbanded?",         "default" => "YES"],
                "others"             => ["label" => "Others",              "default" => ""],
                "repair_pin"         => ["label" => "Join with Pin for Repair",        "default" => "NIL"],
                "repair_box"         => ["label" => "Joint with Box for Repair",       "default" => "NIL"],
                "repair_pin_box"     => ["label" => "Joint with Pin & Box for Repair", "default" => "NIL"],
                "repair_pin_refaced" => ["label" => "Pin Refaced / Rebeveled",         "default" => "NIL"],
                "repair_bent"        => ["label" => "Bent Pipes",                      "default" => "NIL"],
                "premium_qty"        => ["label" => "Premium Qty",         "default" => ""],
                "class2_qty"         => ["label" => "Class 2 Qty",         "default" => "0"],
                "scrap_qty"          => ["label" => "Scrap Qty",           "default" => "0"],
                "total_qty"          => ["label" => "Total Qty",           "default" => ""],
            ],
            "column_groups" => [
                ["group" => "", "cols" => ["id_no" => "ID No."]],
                ["group" => "PIPE BODY", "cols" => [
                    "pb_od_wear"       => "OD Wear",
                    "pb_corrosion_int" => "Corrosion Int",
                    "pb_corrosion_ext" => "Corrosion Ext",
                    "pb_coat_cond"     => "In. Coat Cond",
                    "pb_gauge"         => "Gauge",
                    "pb_slip_cuts"     => "Slip Cuts",
                    "pb_min_wall"      => "Min Wall Thick",
                    "pb_bent"          => "Bent",
                    "pb_emi"           => "EMI",
                ]],
                ["group" => "BOX TOOL JOINT", "cols" => [
                    "box_od"          => "OD",
                    "box_hb_od_cond"  => "HB OD Cond",
                    "box_bevel_dia"   => "Bevel Dia",
                    "box_cb_wall"     => "CB Wall",
                    "box_cb_dia"      => "CB Dia",
                    "box_seal_width"  => "Seal Width",
                    "box_shldr_width" => "Shldr Width",
                    "box_lead_gauge"  => "Lead Gauge",
                    "box_tong_space"  => "Tong Space",
                    "box_cond"        => "Cond",
                    "box_mpi_ut"      => "MPI/UT",
                    "box_upset"       => "Upset",
                    "box_id"          => "ID",
                ]],
                ["group" => "PIN", "cols" => [
                    "pin_od"          => "OD",
                    "pin_bevel_dia"   => "Bevel Dia",
                    "pin_seal_width"  => "Seal Width",
                    "pin_cen_pad_od"  => "Cen Pad OD",
                    "pin_length"      => "Pin Length",
                    "pin_srg_dia"     => "Pin SRG Dia",
                    "pin_srg_width"   => "Pin SRG Width",
                    "pin_lead_gauge"  => "Lead Gauge",
                    "pin_tong_space"  => "Tong Space",
                    "pin_cond"        => "Cond",
                    "pin_mp_ut"       => "MP/UT",
                    "pin_upset"       => "Upset",
                ]],
                ["group" => "", "cols" => [
                    "tally_length" => "Tally Length",
                    "mfr_remark"   => "Manufacturer's Remark",
                    "neck_length"  => "Neck Length",
                ]],
            ],
        ],

        "rotary_sub" => [
            "label"           => "Rotary Connection Report",
            "layout"          => "tally",
            "report_title"    => "ROTARY CONNECTION REPORT",
            "paper"           => "A3",
            "orientation"     => "landscape",
            "owner_label"     => "Name and Address of Equipment Owner",
            "location_label"  => "Location of Test/Examination",
            "date_label"      => "Date of Inspection",
            "tel"             => "08130776837; 08121182863",
            "spec_fields" => [
                "inspection_type"          => ["label" => "Inspection Type",    "default" => "Wet Flourescent Inspection, Visual Inspection, Dimensional Check."],
                "test_procedure"           => ["label" => "Test Procedure",     "default" => "GMSL/NDT/OP/001"],
                "total_box_accepted"       => ["label" => "Total Box Accepted",        "default" => ""],
                "total_box_rejected"       => ["label" => "Total Box Rejected",        "default" => "NILL"],
                "total_pin_accepted"       => ["label" => "Total Pin Accepted",        "default" => ""],
                "total_pin_rejected"       => ["label" => "Total Pin Rejected",        "default" => ""],
                "total_connection_accepted"=> ["label" => "Total Connection Accepted", "default" => ""],
                "total_connection_rejected"=> ["label" => "Total Connection Rejected", "default" => "NILL"],
            ],
            "column_groups" => [
                ["group" => "", "cols" => [
                    "pipe_sn"       => "Pipe S/N",
                    "type_of_tools" => "Type of Tools",
                    "length"        => "Length",
                ]],
                ["group" => "PIN", "cols" => [
                    "pin_thrd"          => "Pin Thhrd",
                    "pin_id"            => "ID",
                    "pin_od"            => "OD Pin",
                    "pin_bev_dia"       => "Bev. Dia",
                    "pin_length"        => "Pin Length",
                    "pin_conn"          => "Conn",
                    "pin_srelief_width" => "S/Relief Width",
                    "pin_srelief_dia"   => "S/Relief Dia",
                    "pin_cond"          => "Cond.",
                ]],
                ["group" => "Tong Space", "cols" => [
                    "ts_pin" => "Pin",
                    "ts_box" => "Box",
                ]],
                ["group" => "BOX", "cols" => [
                    "box_od"              => "OD Box",
                    "box_conn"            => "Conn.",
                    "box_bev_dia"         => "Bev. Dia",
                    "box_bback_length"    => "B/Back Length",
                    "box_bback_dia"       => "B/Back Dia",
                    "box_cbore_dia"       => "C Bore Dia",
                    "box_cb_dept"         => "C/B Dept",
                    "box_floatbore_dia"   => "Float Bore Dia",
                    "box_floatbore_length"=> "Float Bore Length",
                    "box_cond"            => "Cond",
                ]],
                ["group" => "", "cols" => ["remark" => "Remark"]],
            ],
        ],

        "pedestal_crane" => [
            "label"                 => "Pedestal Crane (Thorough Examination)",
            "layout"                => "checklist",
            "owner_label"           => "Equipment Owner / Address",
            "location_label"        => "Inspection Location",
            "date_label"            => "Date of Examination",
            "next_date_label"       => "Next Date of Examination",
            "report_title"          => "CERTIFICATE OF THOROUGH EXAMINATION",
            "compliance"            => "This report complies with the requirements of the Lifting Operations and Lifting Equipment Regulations 1998",
            "reference_standard"    => "STATUTORY INSTRUMENTS 1998 NO.2307, 1992 NO.3073 & 1998 NO.2306",
            "tel"                   => "08130776837, 08121182863",
            "equipment_section_label" => "Certificate & Equipment Details",
            "defects_heading"       => "Observations",
            "defects_prompt"        => "One observation per line (numbered automatically on the certificate). Leave blank if none.",
            // Equipment / header description block (key => label)
            "equipment_fields" => [
                "date_of_issue"        => "Date of Issue",
                "inspection_type_desc" => "Type of Inspection",
                "equipment_name"       => "Equipment Name",
                "manufacturer"         => "Crane Manufacturer",
                "model_no"             => "Model Number",
                "serial_no"            => "Serial Number",
                "power_type"           => "Type/Nature of Power",
                "max_rated_capacity"   => "Maximum Rated Capacity",
                "swl_main_hoist"       => "SWL Main Hoist",
                "swl_aux_line"         => "SWL Aux Line",
                "boom_length"          => "Boom Length",
                "year_built"           => "Year Built",
                "vessel"               => "Vessel",
                "last_exam_date"       => "Date of Last Previous Examination",
                "service"              => "Service",
                "validity"             => "Validity",
            ],
            // Which equipment_fields key becomes the certificate title (falls
            // back to the type label if blank). Most types use "description".
            "title_field" => "equipment_name",
            // No second identity sub-block for this type (unlike winch's wire rope).
            "subblock_label"  => "",
            "subblock_fields" => [],
            // Checklist rating scale (differs from winch's SAT/UNSAT/N/A).
            "checklist_options" => ["1", "2", "3"],
            "checklist_legend"  => "1 = Satisfactory, 2 = Needs Attention, 3 = Needs Repair",
            // Page-2+ checklist: section => [components]. Item labels are kept
            // unique across sections (even where the source form repeats a
            // label like "Hook Condition") since checklist_key() derives the
            // storage key from the item text alone.
            "checklist" => [
                "Boom" => [
                    "Main Boom",
                    "Joint Balls or Pins",
                    "Boom Foot Bearings/Bushes",
                    "Walkways & Handrails (Boom)",
                    "Paint Work (Boom)",
                    "Sheaves/Brackets & Pins (Boom)",
                    "Sheave Guards",
                    "Rope Anchors & Pins (Boom)",
                    "Hoisting Limit Switches (Boom)",
                    "Angle Indicator",
                    "Navigation & Flood Lights",
                    "Helicopter Warning Light",
                ],
                "\"A\" Frame & Support" => [
                    "Condition (A Frame)",
                    "Paint Work (A Frame)",
                    "Sheaves/Brackets & Pins (A Frame)",
                    "Ladders/Walkways & Handrails (A Frame)",
                    "Counter Weights",
                    "Over Boom Stop",
                ],
                "Luff & Suspension Ropes" => [
                    "Pendant Ropes",
                    "Bridle Assembly",
                    "Rope Anchor & Termination",
                    "Socket & Wedges",
                    "Luffing Ropes",
                ],
                "Luffing Hoist Machinery" => [
                    "Operation (Luffing Hoist)",
                    "Winches & Drums (Luffing Hoist)",
                    "Spooling & Rope (Luffing Hoist)",
                    "Mountings (Luffing Hoist)",
                    "Clutch & Brakes (Luffing Hoist)",
                    "Ratchet & Pawl",
                    "Gears & Guards (Luffing Hoist)",
                    "All Limit Switches (Luffing Hoist)",
                ],
                "Main Hoist Rope" => [
                    "Hoist Limit Switches (Main Hoist Rope)",
                    "Rope Condition (Main Hoist Rope)",
                    "Rope Anchor Drum (Main Hoist Rope)",
                    "Brakes (Main Hoist Rope)",
                    "Hoisting Wire Rope (Main Hoist Rope)",
                    "Rope Termination Hook (Main Hoist Rope)",
                ],
                "Main Hook Block" => [
                    "Hook Block Condition (Main)",
                    "Swivel Condition (Main)",
                    "Hook Condition (Main)",
                    "Safety Latch (Main)",
                    "Pin Condition (Main)",
                ],
                "Whip Hoist Rope" => [
                    "Hoist Limit Switches (Whip Hoist Rope)",
                    "Rope Condition (Whip Hoist Rope)",
                    "Rope Anchor Drum (Whip Hoist Rope)",
                    "Brakes (Whip Hoist Rope)",
                    "Hoisting Wire Rope (Whip Hoist Rope)",
                    "Rope Termination Hook (Whip Hoist Rope)",
                ],
                "Whip Hook Block" => [
                    "Hook Block Condition (Whip)",
                    "Swivel Condition (Whip)",
                    "Hook Condition (Whip)",
                    "Safety Latch (Whip)",
                    "Pin Condition (Whip)",
                ],
                "Safe Load Indicator" => [
                    "Position / Visibility",
                    "Markings & Settings",
                    "Manufacturers Rating Charts",
                    "Audible / Visual Function Test",
                    "Lights & Horns",
                    "Radius / Load Indicators",
                ],
                "Hoist Machinery (Whip)" => [
                    "Operation (Whip Hoist Machinery)",
                    "Winches & Drums (Whip Hoist Machinery)",
                    "Spooling of Rope (Whip Hoist Machinery)",
                    "Mountings & Linkages (Whip Hoist Machinery)",
                    "Clutches & Brakes (Whip Hoist Machinery)",
                    "Gears & Guards (Whip Hoist Machinery)",
                    "All Limit Switches (Whip Hoist Machinery)",
                ],
                "Slewing Machinery" => [
                    "Operation (Slewing)",
                    "Mountings & Linkages (Slewing)",
                    "Clutches & Brakes (Slewing)",
                    "Gears & Guards (Slewing)",
                    "Storm Locking Pawl",
                ],
                "Slew Bearing" => [
                    "Bolts",
                    "Lubrication",
                    "Audible Operation",
                    "Movement Under Load",
                    "Rocking Motion Records",
                ],
                "Power Unit" => [
                    "Condition & Operation",
                    "Pipes & Hoses",
                    "Radiator",
                    "Start / Stop",
                    "Controls & Gauges",
                ],
                "Cabin & Controls" => [
                    "Cabin Condition",
                    "Visibility",
                    "Heating & Ventilation",
                    "Access & Egress",
                    "Ladders & Platforms",
                    "Control Markings",
                    "Control Operation",
                    "Windscreen Condition",
                    "Horn & Lights",
                    "Load Radius",
                ],
                "Electrical Components" => [
                    "Switch Gear & Panels",
                    "Connections & Wiring",
                ],
            ],
            // Optional extra pages (only printed on the certificate when
            // filled in — most routine examinations don't include a load
            // test, so these stay out of the way otherwise).
            "hoisting_wire_fields" => [
                "main_hoist_serial"      => "Main Hoist Rope Serial No.",
                "main_hoist_date_fitted" => "Date Rope Fitted (Main Hoist)",
                "aux_hoist_serial"       => "Aux Hoist Rope Serial No.",
                "aux_hoist_date_fitted"  => "Date Rope Fitted (Aux Hoist)",
            ],
            "load_test_columns" => [
                "item"                => "Item",
                "no_of_falls"         => "No of Falls",
                "boom_radius"         => "Boom Radius",
                "boom_angle"          => "Boom Angle (Deg)",
                "swl"                 => "SWL",
                "test_load_dynamic"   => "Test Load Applied (Dynamic)",
                "test_load_static"    => "Test Load Applied (Static)",
                "test_factor"         => "Test Factor Applied",
                "equipment_load_cell" => "Equipment Used (Load Cell)",
                "equipment_water_bag" => "Equipment Used (Water Bag)",
                "remark"              => "Remark",
            ],
            "reeving_columns" => [
                "application"          => "Application",
                "block_manufacturer"   => "Block Manufacturer",
                "block_id"             => "Block ID",
                "no_of_shvs"           => "No. of Shvs",
                "pins"                 => "Pins",
                "retainer"             => "Retainer",
                "sheave_bushing"       => "Sheave Bushing",
                "sheave_condition"     => "Sheave Condition",
                "hook_manufacturer"    => "Hook Manufacturer",
                "hook_id"              => "Hook ID",
                "hook_block_capacity"  => "Hook/Block Capacity (Tons)",
                "hook_throat_opening"  => "Hook Throat/Tram Opening",
                "hook_condition"       => "Hook Condition",
                "bowl_wear"            => "Bowl Wear",
                "point_twist"          => "Point Twist",
                "hook_latch"           => "Hook Latch",
            ],
            "wire_rope_report_columns" => [
                "application"         => "Application",
                "nominal_dia"         => "Nominal Dia",
                "breaking_strength"   => "Breaking Strength",
                "strands"             => "Strands",
                "wires"               => "Wires",
                "lay"                 => "Lay",
                "core"                => "Core",
                "coating"             => "Coating",
                "wire_wear"           => "Wire Wear",
                "corrosion"           => "Corrosion",
                "broken_wires_strand" => "Broken Wires (1 Strand)",
                "broken_wires_lay"    => "Broken Wires (1 Lay)",
                "broken_wires_endfit" => "Broken Wires (End Fit)",
                "end_fit_cond"        => "End Fit Cond",
                "kink"                => "Kink",
                "crush"               => "Crush",
                "accum_defects"       => "Accum Defects",
                "max_load_1part"      => "Max Load 1-Part (lbs)",
                "parts"               => "Parts",
                "certificate_no"      => "Certificate No",
                "length"              => "Length",
            ],
        ],
    ];

    // Winch Non-Conformance Report: same equipment/checklist as "winch" above,
    // reused rather than duplicated, just filed under a different report title
    // (and defects wording) for when the outcome is a standalone defect notice.
    $types["winch_defect"] = [
        "label"              => "Winch Non-Conformance Report",
        "layout"             => "checklist",
        "owner_label"        => $types["winch"]["owner_label"],
        "report_title"       => "NON-CONFORMANCE REPORT",
        "compliance"         => $types["winch"]["compliance"],
        "reference_standard" => $types["winch"]["reference_standard"],
        "tel"                => $types["winch"]["tel"],
        "equipment_fields"   => $types["winch"]["equipment_fields"],
        "subblock_label"     => $types["winch"]["subblock_label"],
        "subblock_fields"    => $types["winch"]["subblock_fields"],
        "checklist"          => $types["winch"]["checklist"],
        "defects_heading"    => "Identification of Defect",
        "defects_prompt"     => "Identification of any part found to have a defect which could become a danger to a person and a description of the defect (if none, state NONE)",
    ];

    // Electric Chain Hoist Non-Conformance Report. Own equipment fields and
    // checklist (no wire-rope sub-block, unlike winch). The "subblock" slot is
    // repurposed to carry the page-2 Remark/Recommendations fields instead of
    // a second equipment block — the template places them after the checklist.
    $types["chain_hoist_defect"] = [
        "label"              => "Electric Chain Hoist Non-Conformance Report",
        "layout"             => "checklist",
        "owner_label"        => "Equipment Owner / Address",
        "report_title"       => "NON-CONFORMANCE REPORT",
        "compliance"         => "This report complies with the requirements of the Lifting Operations and Lifting Equipment Regulations 1998",
        "reference_standard" => "BS EN 14492-1:2006",
        "tel"                => "08130776837, 08121182863",
        "equipment_fields" => [
            "description"             => "Description",
            "serial_no"               => "I.D/Serial No.",
            "model_no"                => "Model No.",
            "manufacturer"            => "Manufacturer",
            "year_of_manufacture"     => "Year of Manufacture",
            "swl"                     => "SWL",
            "dimension"               => "Diamension",
            "number_of_fall"          => "Number of Fall",
            "equipment_test_location" => "Equipment Test Location",
        ],
        "subblock_label"  => "Remark & Recommendations",
        "subblock_fields" => [
            "remark"          => "Remark",
            "recommendations" => "Recommendations (one per line)",
        ],
        "subblock_textarea_fields" => ["recommendations"],
        "checklist" => [
            "Certification Information" => [
                "Last Inspection Report",
                "Maintenance Logbook",
            ],
            "Physical Marking" => [
                "Identification Number(s)",
            ],
            "Anchor Fixing" => [
                "Support Structure",
            ],
            "Accessories" => [],
            "Hoist System / Drive Unit" => [
                "Chain Condition",
                "Emergency Stop / Breaking System",
                "Extension Cords",
                "Power Supply Adequacy",
                "Connection And Wiring",
                "Operating Control",
                "Hook Condition Top and Bottom",
            ],
        ],
        "defects_heading" => "Identification of Defect",
        "defects_prompt"  => "Identification of any part found to have a defect which could become a danger to person or Equipment; state description of defect (if none state NONE)",
    ];

    // Pedestal Crane (Starboard): same certificate shape as "pedestal_crane"
    // above (STARBOARD.pdf uses identical fields/checklist/optional pages to
    // PORTSIDE.pdf) but filed as its own type since it's a distinct crane unit.
    $types["pedestal_crane_starboard"] = [
        "label"                    => "Pedestal Crane (Starboard, Thorough Examination)",
        "layout"                   => "checklist",
        "owner_label"              => $types["pedestal_crane"]["owner_label"],
        "location_label"           => $types["pedestal_crane"]["location_label"],
        "date_label"               => $types["pedestal_crane"]["date_label"],
        "next_date_label"          => $types["pedestal_crane"]["next_date_label"],
        "report_title"             => $types["pedestal_crane"]["report_title"],
        "compliance"               => $types["pedestal_crane"]["compliance"],
        "reference_standard"       => $types["pedestal_crane"]["reference_standard"],
        "tel"                      => $types["pedestal_crane"]["tel"],
        "equipment_section_label"  => $types["pedestal_crane"]["equipment_section_label"],
        "defects_heading"          => $types["pedestal_crane"]["defects_heading"],
        "defects_prompt"           => $types["pedestal_crane"]["defects_prompt"],
        "equipment_fields"         => $types["pedestal_crane"]["equipment_fields"],
        "title_field"              => $types["pedestal_crane"]["title_field"],
        "subblock_label"           => $types["pedestal_crane"]["subblock_label"],
        "subblock_fields"          => $types["pedestal_crane"]["subblock_fields"],
        "checklist_options"        => $types["pedestal_crane"]["checklist_options"],
        "checklist_legend"         => $types["pedestal_crane"]["checklist_legend"],
        "checklist"                => $types["pedestal_crane"]["checklist"],
        "hoisting_wire_fields"     => $types["pedestal_crane"]["hoisting_wire_fields"],
        "load_test_columns"        => $types["pedestal_crane"]["load_test_columns"],
        "reeving_columns"          => $types["pedestal_crane"]["reeving_columns"],
        "wire_rope_report_columns" => $types["pedestal_crane"]["wire_rope_report_columns"],
    ];

    return $types;
}

/** Flatten a tally type's column_groups to an ordered [key => label] map. */
function tally_columns(array $def): array
{
    $out = [];
    foreach ($def["column_groups"] ?? [] as $g) {
        foreach ($g["cols"] as $k => $label) {
            $out[$k] = $label;
        }
    }
    return $out;
}

/** Return one type definition or null. */
function inspection_type(string $key): ?array
{
    return inspection_types()[$key] ?? null;
}

/** Stable list of checklist components for a type (for validation/iteration). */
function checklist_components(array $def): array
{
    $out = [];
    foreach ($def["checklist"] as $section => $items) {
        foreach ($items as $item) {
            $out[] = ["section" => $section, "item" => $item];
        }
    }
    return $out;
}

/** A short, deterministic key for a checklist component (used as form name). */
function checklist_key(string $item): string
{
    return substr(md5($item), 0, 12);
}

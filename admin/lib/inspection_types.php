<?php
/**
 * Registry of inspection types the system can GENERATE.
 * Add a new type by adding an entry here and a matching template in
 * admin/templates/<key>.php. Everything else (form, PDF, storage) is driven
 * by this definition, so new types need no schema changes.
 */

function inspection_types(): array
{
    return [
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
    ];
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

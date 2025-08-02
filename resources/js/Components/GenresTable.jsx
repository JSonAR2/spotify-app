import * as React from "react";
import { DataGrid } from "@mui/x-data-grid";
import Paper from "@mui/material/Paper";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faPlay, faPause } from "@fortawesome/free-solid-svg-icons";
import axios from "axios";
import { Button } from "@mui/material";

const columns = [
    { field: "id", headerName: "ID" },
    { field: "name", headerName: "Name", width: 150 },
    { field: "track_count", headerName: "Track Count", width: 130 },
];

const paginationModel = { page: 0, pageSize: 20 };

export default function GenresTable({ genres }) {
    return (
        <Paper sx={{ width: "100%" }}>
            <DataGrid
                rows={genres}
                columns={columns}
                initialState={{
                    pagination: { paginationModel },
                    columns: { columnVisibilityModel: { id: false } },
                }}
                pageSizeOptions={[5, 10]}
                checkboxSelection
                disableVirtualization
                sx={{ border: 0 }}
                disableRowSelectionOnClick
                // onRowClick={handleRowClick}
            />
        </Paper>
    );
}

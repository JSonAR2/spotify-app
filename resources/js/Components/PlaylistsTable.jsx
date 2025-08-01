import * as React from "react";
import { DataGrid } from "@mui/x-data-grid";
import Paper from "@mui/material/Paper";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
    faPlay,
    faPause,
    faTrash,
    faEye,
} from "@fortawesome/free-solid-svg-icons";
import axios from "axios";
import { router } from "@inertiajs/react";

const columns = [
    { field: "id", headerName: "ID", width: 20 },
    { field: "name", headerName: "Name", width: 300 },
    { field: "description", headerName: "Description", width: 400 },
    {
        field: "track_count",
        headerName: "Track Count",
        width: 130,
    },
    {
        field: "collaborative",
        headerName: "Collaborative",
        width: 130,
    },
    {
        field: "actions",
        sortable: false,
        headerName: "Actions",
        width: 130,
        renderCell: (params) => (
            <div className="flex flex-wrap content-center h-full space-x-5">
                <FontAwesomeIcon
                    icon={faEye}
                    onClick={() => viewTracks(params.row.id)}
                />
                <FontAwesomeIcon
                    icon={faTrash}
                    onClick={() => handleDelete(params.row.id)}
                />
            </div>
        ),
    },
];

const handleDelete = (id) => {
    if (confirm("Are you sure you want to delete this playlist?")) {
        axios
            .delete(`/playlists/delete_playlist?id=${id}`)
            .then((response) => {
                console.log("Playlist deleted", response.data);
                router.reload();
            })
            .catch((error) => {
                console.error(
                    "There was an error deleting the playlist!",
                    error
                );
            });
    }
};

const viewTracks = (id) => {
    router.visit(`/playlists/view_playlist/${id}`);
};

const paginationModel = { page: 0, pageSize: 20 };

export default function PlaylistsTable({ playlists }) {
    return (
        <Paper sx={{ height: 800, width: "100%" }}>
            <DataGrid
                rows={playlists}
                columns={columns}
                initialState={{ pagination: { paginationModel } }}
                pageSizeOptions={[5, 10]}
                checkboxSelection
                disableVirtualization
                sx={{ border: 0 }}
            />
        </Paper>
    );
}

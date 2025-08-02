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
    { field: "album_name", headerName: "Album", width: 130 },
    { field: "artist_name", headerName: "Artist", width: 130 },
    { field: "playlists_count", headerName: "Playlist Count", width: 130 },
    {
        field: "genres",
        headerName: "Genres",
        width: 130,
    },
    {
        field: "popularity",
        headerName: "Popularity",
        width: 130,
    },
    // {
    //     field: "acousticness",
    //     headerName: "Acouticness",
    //     width: 130,
    //     valueGetter: (value, row) =>
    //         `${parseInt(row.acousticness * 100) + "%" || ""}`,
    // },
    // {
    //     field: "danceability",
    //     headerName: "Danceability",
    //     width: 130,
    //     valueGetter: (value, row) =>
    //         `${parseInt(row.danceability * 100) + "%" || ""}`,
    // },
    // {
    //     field: "energy",
    //     headerName: "Energy",
    //     width: 130,
    //     valueGetter: (value, row) =>
    //         `${parseInt(row.energy * 100) + "%" || ""}`,
    // },
    // {
    //     field: "instrumentalness",
    //     headerName: "Instrumentalness",
    //     width: 130,
    //     valueGetter: (value, row) =>
    //         `${parseInt(row.instrumentalness * 100) + "%" || ""}`,
    // },
    // {
    //     field: "tempo",
    //     headerName: "Tempo",
    //     width: 130,
    //     valueGetter: (value, row) => `${parseInt(row.tempo) + " BPM" || ""}`,
    // },
    // {
    //     field: "valence",
    //     headerName: "Happiness",
    //     width: 130,
    //     valueGetter: (value, row) =>
    //         `${parseInt(row.valence * 100) + "%" || ""}`,
    // },
    // {
    //     field: "preview_url",
    //     headerName: "Preview",
    //     sortable: false,
    //     width: 160,
    //     renderCell: function (params) {
    //         if (params.row.preview_link) {
    //             return (
    //                 <div>
    //                     <FontAwesomeIcon icon={faPlay} />
    //                     <audio
    //                         id={params.row.track_id}
    //                         src={params.row.preview_link}
    //                     ></audio>
    //                 </div>
    //             );
    //         }
    //         return <div>No preview</div>;
    //     },
    // },
    {
        field: "play",
        headerName: "Play",
        sortable: false,
        width: 100,
        type: "actions",
        renderCell: function (params) {
            return (
                <div>
                    <FontAwesomeIcon
                        icon={faPlay}
                        onClick={() => playTrack(params.row.id)}
                        style={{ cursor: "pointer" }}
                    />
                </div>
            );
        },
    },
    {
        field: "add_to_queue",
        headerName: "Add to Queue",
        sortable: false,
        width: 130,
        type: "actions",
        renderCell: function (params) {
            return (
                <div>
                    <Button
                        icon={<FontAwesomeIcon icon={faPlay} />}
                        variant="outlined"
                        size="small"
                        onClick={() => addToQueue(params.row.id)}
                    >
                        Queue
                    </Button>
                </div>
            );
        },
    },
    {
        field: "play_album",
        headerName: "Play Album",
        sortable: false,
        width: 130,
        type: "actions",
        renderCell: function (params) {
            return (
                <div>
                    <Button
                        variant="outlined"
                        size="small"
                        onClick={() => playAlbum(params.row.album_id)}
                    >
                        Play Album
                    </Button>
                </div>
            );
        },
    },
];

const playTrack = (track_id) => {
    axios
        .post("/spotify/play_track", { track_id })
        .then((response) => {
            console.log("Track played", response.data);
        })
        .catch((error) => {
            console.error("There was an error playing the track!", error);
        });
};

const addToQueue = (track_id) => {
    axios
        .post("/spotify/add_track_to_queue", { track_id })
        .then((response) => {
            console.log("Track added to queue", response.data);
        })
        .catch((error) => {
            console.error(
                "There was an error adding the track to the queue!",
                error
            );
        });
};

const playAlbum = (album_id) => {
    axios
        .post("/spotify/play_album", { album_id })
        .then((response) => {
            console.log("Album played", response.data);
        })
        .catch((error) => {
            console.error("There was an error playing the album!", error);
        });
};

// const playAudio = (track_id) => {
// const audio = document.getElementById(track_id);
// const all_audio = document.getElementsByTagName("audio");
// for (let i = 0; i < all_audio.length; i++) {
//     const track = all_audio[i];
//     if (track.id !== track_id) {
//         track.pause();
//         track.currentTime = 0;
//     }
// }
// if (audio.paused) {
//     audio.play();
// } else {
//     audio.pause();
//     audio.currentTime = 0;
// }
// };

// const handleRowClick = (
//     params, // GridRowParams
//     event, // MuiEvent<React.MouseEvent<HTMLElement>>
//     details // GridCallbackDetails
// ) => {
//     // console.log(params.row.preview_link);
//     // console.log(params.row.track_id);
//     // if (params.row.preview_link) {
//     playAudio(params.row.id);
//     // }
// };

const paginationModel = { page: 0, pageSize: 20 };

export default function TracksTable({ tracks }) {
    return (
        <Paper sx={{ width: "100%" }}>
            <DataGrid
                rows={tracks}
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

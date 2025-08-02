"use client";
import * as React from "react";

export const BASE_URL = "http://localhost:8000";

function decodeParams(url) {
    const params = new URL(url).searchParams;
    const decodedParams = {};
    const array = Array.from(params.entries());

    for (const [key, value] of array) {
        try {
            decodedParams[key] = JSON.parse(value);
        } catch {
            decodedParams[key] = value;
        }
    }

    return decodedParams;
}

const getInitialState = (columns, groupingField) => {
    const columnVisibilityModel = {};
    columns.forEach((col) => {
        if (col.hide) {
            columnVisibilityModel[col.field] = false;
        }
    });

    if (groupingField) {
        columnVisibilityModel[groupingField] = false;
    }

    return { columns: { columnVisibilityModel } };
};

function sendEmptyResponse() {
    return new Promise((resolve) => {
        resolve({ rows: [], rowCount: 0 });
    });
}

export const useMockServer = (
    dataSetOptions,
    serverOptions,
    shouldRequestsFail,
    nestedPagination
) => {
    const dataRef = React.useRef(null);
    const [isDataReady, setDataReady] = React.useState(false);
    const [index, setIndex] = React.useState(0);
    const shouldRequestsFailRef = React.useRef(shouldRequestsFail ?? false);

    console.log("DataSet options:", dataSetOptions);
    console.log("Server options:", serverOptions);
    const isTreeData = dataSetOptions?.isTreeData ?? false;
    React.useEffect(() => {
        if (shouldRequestsFail !== undefined) {
            shouldRequestsFailRef.current = shouldRequestsFail;
        }
    }, [shouldRequestsFail]);

    const isRowGrouping = dataSetOptions?.rowGrouping ?? false;

    const columns = dataSetOptions.static_columns;

    const initialState = React.useMemo(
        () => getInitialState(columns, false),
        [columns]
    );

    const getGroupKey = React.useMemo(() => {
        if (isTreeData) {
            return (row) => row[options.treeData.groupingField];
        }
        return undefined;
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [isTreeData]);

    const getChildrenCount = React.useMemo(() => {
        if (isTreeData) {
            return (row) => row.descendantCount;
        }
        return undefined;
    }, [isTreeData]);

    // React.useEffect(() => {
    //     const cacheKey = `${options.dataSet}-${options.rowLength}-${index}-${options.maxColumns}`;

    //     // Cache to allow fast switch between the JavaScript and TypeScript version
    //     // of the demos.
    //     if (dataCache.has(cacheKey)) {
    //         const newData = dataCache.get(cacheKey);
    //         dataRef.current = newData;
    //         setDataReady(true);
    //         return undefined;
    //     }

    //     if (options.dataSet === "Movies") {
    //         const rowsData = { rows: getMovieRows(), columns };
    //         dataRef.current = rowsData;
    //         setDataReady(true);
    //         dataCache.set(cacheKey, rowsData);
    //         return undefined;
    //     }

    //     let active = true;

    //     (async () => {
    //         let rowData;
    //         const rowLength = options.rowLength;
    //         if (rowLength > 1000) {
    //             rowData = await getRealGridData(1000, columns);
    //             rowData = await extrapolateSeed(rowLength, rowData);
    //         } else {
    //             rowData = await getRealGridData(rowLength, columns);
    //         }

    //         if (!active) {
    //             return;
    //         }

    //         if (isTreeData) {
    //             rowData = addTreeDataOptionsToDemoData(rowData, {
    //                 maxDepth: options.treeData?.maxDepth,
    //                 groupingField: options.treeData?.groupingField,
    //                 averageChildren: options.treeData?.averageChildren,
    //             });
    //         }

    //         if (process.env.NODE_ENV !== "production") {
    //             deepFreeze(rowData);
    //         }

    //         dataCache.set(cacheKey, rowData);
    //         dataRef.current = rowData;
    //         setDataReady(true);
    //     })();

    //     return () => {
    //         active = false;
    //     };
    // }, [
    //     columns,
    //     isTreeData,
    //     options.rowLength,
    //     options.treeData?.maxDepth,
    //     options.treeData?.groupingField,
    //     options.treeData?.averageChildren,
    //     options.dataSet,
    //     options.maxColumns,
    //     index,
    // ]);

    const fetchRows = React.useCallback(
        async (requestUrl) => {
            if (!requestUrl || !isDataReady) {
                return sendEmptyResponse();
            }
            const params = decodeParams(requestUrl);
            const verbose = serverOptions?.verbose ?? true;
            // eslint-disable-next-line no-console
            const print = console.info;
            if (verbose) {
                print("MUI X: DATASOURCE REQUEST", params);
            }
            let getRowsResponse;
            const serverOptionsWithDefault = {
                minDelay:
                    serverOptions?.minDelay ?? DEFAULT_SERVER_OPTIONS.minDelay,
                maxDelay:
                    serverOptions?.maxDelay ?? DEFAULT_SERVER_OPTIONS.maxDelay,
                useCursorPagination:
                    serverOptions?.useCursorPagination ??
                    DEFAULT_SERVER_OPTIONS.useCursorPagination,
            };

            if (shouldRequestsFailRef.current) {
                const { minDelay, maxDelay } = serverOptionsWithDefault;
                const delay = randomInt(minDelay, maxDelay);
                return new Promise((_, reject) => {
                    if (verbose) {
                        print("MUI X: DATASOURCE REQUEST FAILURE", params);
                    }
                    setTimeout(
                        () => reject(new Error("Could not fetch the data")),
                        delay
                    );
                });
            }

            if (isTreeData) {
                const { rows, rootRowCount, aggregateRow } =
                    await processTreeDataRows(
                        dataRef.current?.rows ?? [],
                        params,
                        serverOptionsWithDefault,
                        columnsWithDefaultColDef,
                        nestedPagination ?? false
                    );

                getRowsResponse = {
                    rows: rows
                        .slice()
                        .map((row) => ({ ...row, path: undefined })),
                    rowCount: rootRowCount,
                    ...(aggregateRow ? { aggregateRow } : {}),
                };
            } else if (isRowGrouping) {
                const { rows, rootRowCount, aggregateRow } =
                    await processRowGroupingRows(
                        dataRef.current?.rows ?? [],
                        params,
                        serverOptionsWithDefault,
                        columnsWithDefaultColDef
                    );

                getRowsResponse = {
                    rows: rows
                        .slice()
                        .map((row) => ({ ...row, path: undefined })),
                    rowCount: rootRowCount,
                    ...(aggregateRow ? { aggregateRow } : {}),
                };
            } else {
                const {
                    returnedRows,
                    nextCursor,
                    totalRowCount,
                    aggregateRow,
                } = await loadServerRows(
                    dataRef.current?.rows ?? [],
                    { ...params, ...params.paginationModel },
                    serverOptionsWithDefault,
                    columnsWithDefaultColDef
                );
                getRowsResponse = {
                    rows: returnedRows,
                    rowCount: totalRowCount,
                    pageInfo: { nextCursor },
                    ...(aggregateRow ? { aggregateRow } : {}),
                };
            }

            return (
                new Promise() <
                T >
                ((resolve) => {
                    if (verbose) {
                        print(
                            "MUI X: DATASOURCE RESPONSE",
                            params,
                            getRowsResponse
                        );
                    }
                    resolve(getRowsResponse);
                })
            );
        },
        [
            dataRef,
            isDataReady,
            serverOptions?.verbose,
            serverOptions?.minDelay,
            serverOptions?.maxDelay,
            serverOptions?.useCursorPagination,
            isTreeData,
            {},
            nestedPagination,
            isRowGrouping,
        ]
    );

    const editRow = React.useCallback(
        async (rowId, updatedRow) => {
            return new Promise((resolve, reject) => {
                const minDelay =
                    serverOptions?.minDelay ?? DEFAULT_SERVER_OPTIONS.minDelay;
                const maxDelay =
                    serverOptions?.maxDelay ?? DEFAULT_SERVER_OPTIONS.maxDelay;
                const delay = randomInt(minDelay, maxDelay);

                const verbose = serverOptions?.verbose ?? true;
                // eslint-disable-next-line no-console
                const print = console.info;
                if (verbose) {
                    print("MUI X: DATASOURCE EDIT ROW REQUEST", {
                        rowId,
                        updatedRow,
                    });
                }

                if (shouldRequestsFailRef.current) {
                    setTimeout(
                        () =>
                            reject(
                                new Error(
                                    `Could not update the row with the id ${rowId}`
                                )
                            ),
                        delay
                    );
                    if (verbose) {
                        print("MUI X: DATASOURCE EDIT ROW FAILURE", {
                            rowId,
                            updatedRow,
                        });
                    }
                    return;
                }

                const newRows = [...(dataRef.current?.rows || [])];
                const rowIndex =
                    newRows.findIndex((row) => row.id === rowId) ?? -1;
                if (rowIndex === -1) {
                    return;
                }
                newRows[rowIndex] = updatedRow;
                const newData = { ...dataRef.current, rows: newRows };
                const cacheKey = `${options.dataSet}-${options.rowLength}-${index}-${options.maxColumns}`;
                dataCache.set(cacheKey, newData);
                setTimeout(() => {
                    if (verbose) {
                        print("MUI X: DATASOURCE EDIT ROW SUCCESS", {
                            rowId,
                            updatedRow,
                        });
                    }
                    resolve(updatedRow);
                }, delay);
                dataRef.current = newData;
            });
        },
        [
            index,

            serverOptions?.maxDelay,
            serverOptions?.minDelay,
            serverOptions?.verbose,
        ]
    );

    return {
        columns: columns,
        initialState: initialState,
        getGroupKey,
        getChildrenCount,
        fetchRows,
        editRow,
        loadNewData: () => {
            setIndex((oldIndex) => oldIndex + 1);
        },
        isReady: isDataReady,
    };
};

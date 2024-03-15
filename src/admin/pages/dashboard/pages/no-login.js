/* WordPress */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/* Library */
import classNames from 'classnames';
import { cloneDeep } from 'lodash';

/*Atrc*/
import {
	AtrcText,
	AtrcWireFrameContentSidebar,
	AtrcWireFrameHeaderContentFooter,
	AtrcPrefix,
	AtrcPanelBody,
	AtrcPanelRow,
	AtrcContent,
	AtrcTitleTemplate1,
	AtrcNestedObjUpdateByKey1,
	AtrcControlSelectPost,
} from 'atrc';

/* Inbuilt */
import { AtrcReduxContextData } from '../../../routes';
import { DocsTitle } from '../../../components/molecules';

/*Local*/
const MainContent = () => {
	const data = useContext(AtrcReduxContextData);

	const { dbSettings, dbUpdateSetting } = data;

	const { dashboard = {} } = dbSettings;

	const { noLoginContent = 0 } = dashboard;

	return (
		<AtrcContent className={classNames(AtrcPrefix('cont-dashboard'))}>
			<AtrcPanelRow className={classNames('at-m')}>
				<AtrcControlSelectPost
					label={__('Select page for non-logged-in visitor', 'brand-master')}
					wrapProps={{
						className: 'at-flx-grw-1',
					}}
					value={noLoginContent}
					onChange={(newVal) => {
						const updatedSettings = AtrcNestedObjUpdateByKey1({
							settings: dashboard,
							key1: 'noLoginContent',
							val1: newVal,
						});
						dbUpdateSetting('dashboard', updatedSettings);
					}}
					postType='page'
				/>
			</AtrcPanelRow>
		</AtrcContent>
	);
};

const Documentation = () => {
	const data = useContext(AtrcReduxContextData);

	const { lsSettings, lsSaveSettings } = data;

	return (
		<AtrcWireFrameHeaderContentFooter
			headerRowProps={{
				className: classNames(AtrcPrefix('header-docs'), 'at-m'),
			}}
			renderHeader={
				<DocsTitle
					onClick={() => {
						const localStorageClone = cloneDeep(lsSettings);
						localStorageClone.bmdSdDocs1 = !localStorageClone.bmdSdDocs1;
						lsSaveSettings(localStorageClone);
					}}
				/>
			}
			renderContent={
				<>
					<AtrcPanelBody
						className={classNames(AtrcPrefix('m-0'))}
						title={__(
							'When does the content of the "Select Page For Non-Logged-in Visitor" appear?',
							'brand-master'
						)}
						initialOpen={true}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								'If a user navigates directly to the frontend dashboard without logging in, the content of the chosen page on the "Select Page For Non-Logged-In Visitor" section will be displayed for them.',
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
				</>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

const Settings = () => {
	const data = useContext(AtrcReduxContextData);
	const { lsSettings } = data;

	const { bmdSdDocs1 } = lsSettings;

	return (
		<AtrcWireFrameHeaderContentFooter
			wrapProps={{
				className: classNames(AtrcPrefix('bg-white'), 'at-bg-cl'),
			}}
			renderHeader={
				<AtrcTitleTemplate1
					title={__('Frontend Dashboard settings', 'brand-master')}
				/>
			}
			renderContent={
				<AtrcWireFrameContentSidebar
					wrapProps={{
						allowContainer: true,
						type: 'fluid',
						tag: 'section',
					}}
					renderContent={<MainContent />}
					renderSidebar={!bmdSdDocs1 ? <Documentation /> : null}
					contentProps={{
						contentCol: bmdSdDocs1 ? 'at-col-12' : 'at-col-7',
					}}
					sidebarProps={{
						sidebarCol: 'at-col-5',
					}}
				/>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

export default Settings;
